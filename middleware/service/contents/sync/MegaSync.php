<?php
namespace middleware\service\contents\sync;

use framework\library\Log;
use framework\library\sql_builder\SqlBuilder;
use framework\library\String;
use framework\library\Time;
use middleware\exception\SynchronizerException;
use middleware\model\ContentGrade;
use middleware\model\Movie;
use middleware\service\contents\crawler\MegaCrawler;
use middleware\service\contents\MegaContents;
use middleware\service\contents\parser\MegaParser;

class MegaSync extends MegaContents
{
    /**
     * @var MegaParser
     */
    public $parser = null;

    /**
     * @var MegaCrawler
     */
    public $crawler = null;

    public function __construct()
    {
        parent::__construct(CONTENT_TYPE_MOVIE);

        $this->crawler = new MegaCrawler();
        $this->parser = $this->crawler->parser;
    }

    public function syncAllContentId()
    {
        $movie_tree = null;
        $movies = $this->crawler->getBoxOfficeList($movie_tree);
        if (empty($movies)) {
            Log::error('메가박스 박스오피스 데이터 받아오기 실패');

            return false;
        }

        $movie_list = $this->parser->parseMovieList($movie_tree);

        if (empty($movie_list)) {
            Log::error('메가박스 박스오피스 데이터 파싱 실패');

            return false;
        }

        $result = array('success' => 0, 'fail' => 0);
        foreach ($movie_list as $movie) {
            $ret = $this->syncContentId($movie);
            if ($ret) {
                $result['success']++;
            } else {
                $result['fail']++;
            }
        }

        Log::info('메가박스 아이디 업데이트 종료', $result);

        return $result;
    }

    public function syncContentId($movie)
    {
        if (empty($movie['title']) || empty($movie['mega_id']) || empty($movie['limit_grade'])) {
            throw new SynchronizerException('영화 데이터에 제목이나 메가박스 아이디나 영화등급 없음', $movie);
        }
        $title = $movie['title'];
        $mega_id = $movie['mega_id'];
        $limit_grade = $movie['limit_grade'];

        $movie_model = Movie::getInstance();
        $where = array('title' => $title);//, 'limit_grade' => $limit_grade);

        if (!empty($movie['release_date'])) {
            $where[] = SqlBuilder::orWhere(array('release_date' => $movie['release_date'], 're_release_date' => $movie['release_date']));
        }
        $local_movie = $movie_model->getRow($where, 'release_date desc');
        if (empty($local_movie)) {
            $where = array("TRIM(replace(Replace(Replace(title,'\t',''),'\r',''), ' ', ''))" => trim(String::stripAllWhiteSpaces($title, '')));//, 'limit_grade' => $limit_grade);
            if (!empty($movie['release_date'])) {
                $where[] = SqlBuilder::orWhere(array('release_date' => $movie['release_date'], 're_release_date' => $movie['release_date']));
            }
            $local_movie = $movie_model->getRow($where, 'release_date desc');
            if (empty($local_movie)) {
                //                $local_movie = $movie_model->getRow(array('title' => $title));
                //                if (empty($local_movie)) {
                //                    $local_movie = $movie_model->getRow(
                //                        array(
                //                            "TRIM(replace(Replace(Replace(title,'\t',''),'\r',''), ' ', ''))" => trim(String::stripAllWhiteSpaces($title, ''))
                //                        )
                //                    );
                //                    if (empty($local_movie)) {
                Log::warning('매칭되는 영화 없음', array('mega_title' => $title, 'mega_release_date' => $movie['release_date']));

                return false;
                //                    } else {
                //                        Log::warning('영화 제목만으로 매핑(strip white space)', array($movie, $local_movie));
                //                    }
                //                } else {
                //                    Log::warning('영화 제목만으로 매핑', array($movie, $local_movie));
                //                }
            }
        }

        $ret = $movie_model->modify(array('mega_id' => $mega_id), array('movie_id' => $local_movie['movie_id']));
        if (!$ret) {
            Log::error('메가박스 아이디 업데이트 실패', array('mega_id' => $mega_id, 'movie_id' => $local_movie['movie_id']));

            return false;
        }

        Log::info('메가박스 아이디 업데이트 성공', array('movie_id' => $local_movie['movie_id'], 'mega_id' => $mega_id, 'title' => $local_movie['title']));

        return true;
    }

    public function syncMovieRate($content_id, $update_history = true)
    {
        if (empty($content_id)) {
            return false;
        }

        $movie_model = Movie::getInstance();

        $content = $movie_model->getRow(array('movie_id' => $content_id));
        if (empty($content)) {
            Log::error('Content DB 검색 실패', compact('content_id'));

            return false;
        }

        if (empty($content['mega_id'])) {
            Log::error('메가박스 ID 없음', compact('content_id'));

            return false;
        }

        $content_tree = null;
        $content_html = $this->crawler->getContent($content['mega_id'], $content_tree);
        if (empty($content_html) || empty($content_tree)) {
            Log::error('Content 메가박스에서 에서 조회 실패', compact('content_id'));

            return false;
        }

        $score_box_tree = $content_tree->find('.right_wrap .reservation_wrap .left_p');
        if (!$score_box_tree->exists()) {
            Log::error('평점 엘리먼트 없음', compact('content_id'));

            return false;
        }

        $rates = $this->parser->extractAvgRate($score_box_tree);

        if (empty($rates)) {
            Log::error('평점 추출 실패', compact('content_id'));

            return false;
        }

        if ($rates['count'] < 1) {
            Log::error('평점 없음', compact('content_id'));

            return false;
        }

        $rates['point'] = $rates['point'] * 10;

        $movie_model->begin();
        $ret = $movie_model->modify(array('mega_grade_point' => $rates['point'], 'mega_grade_count' => $rates['count']), array('movie_id' => $content_id));
        if (!$ret) {
            Log::error('영화 테이블에 rate 업데이트 실패', compact('content_id'));
            $movie_model->rollBack();

            return false;
        }

        if ($update_history) {
            $grade_model = ContentGrade::getInstance();
            $insert_date = Time::Ymd();
            $grade_where = array(
                'content_id'   => $content_id,
                'grade_vendor' => CONTENTS_PROVIDER_MEGA,
                'insert_date'  => $insert_date
            );

            if (!$grade_model->exist($grade_where)) {
                $grade_data = array(
                    'content_id'   => $content_id,
                    'grade_vendor' => CONTENTS_PROVIDER_MEGA,
                    'grade_point'  => $rates['point'],
                    'grade_count'  => $rates['count'],
                    'insert_date'  => $insert_date
                );

                $ret = $grade_model->add($grade_data);

                if (!$ret) {
                    Log::error('유저 평가 테이블에 업데이트 실패', compact('content_id'));
                    $movie_model->rollBack();

                    return false;
                }
            } else {
                Log::warning('유저 평가 테이블에 이미 존재함', compact('content_id'));
            }
        }

        Log::info('메가박스 평점 동기화 성공', compact('content_id'));
        $movie_model->commit();

        return true;
    }
}