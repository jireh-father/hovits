<?php
namespace middleware\service\contents\sync;

use framework\library\Log;
use framework\library\sql_builder\SqlBuilder;
use framework\library\String;
use framework\library\Time;
use middleware\exception\SynchronizerException;
use middleware\model\ContentGrade;
use middleware\model\Movie;
use middleware\service\contents\crawler\LotteCrawler;
use middleware\service\contents\LotteContents;
use middleware\service\contents\parser\LotteParser;

class LotteSync extends LotteContents
{
    /**
     * @var LotteParser
     */
    public $parser = null;

    /**
     * @var LotteCrawler
     */
    public $crawler = null;

    public function __construct()
    {
        parent::__construct(CONTENT_TYPE_MOVIE);

        $this->crawler = new LotteCrawler();
        $this->parser = $this->crawler->parser;
    }

    public function syncAllContentId()
    {
        $movies = $this->crawler->getBoxOfficeList();
        if (empty($movies)) {
            Log::error('롯데시네마 박스오피스 데이터 받아오기 실패');

            return false;
        }

        $result = array('success' => 0, 'fail' => 0);
        foreach ($movies as $movie) {
            $ret = $this->syncContentId($movie);
            if ($ret) {
                $result['success']++;
            } else {
                $result['fail']++;
            }
        }

        Log::info('롯데 아이디 업데이트 종료', $result);

        return $result;
    }

    public function syncContentId($movie)
    {
        if (empty($movie['contentTitle']) || empty($movie['openingDate'])) {
            throw new SynchronizerException('영화 데이터에 제목이나 개봉일 없음', $movie);
        }
        $title = $movie['contentTitle'];
        $release_date = $movie['openingDate'];

        $movie_model = Movie::getInstance();
        $local_movie = $movie_model->getRow(array('title' => $title, SqlBuilder::orWhere(array('release_date' => $release_date, 're_release_date' => $release_date))));

        if (empty($local_movie)) {
            $local_movie = $movie_model->getRow(
                array(
                    "TRIM(replace(Replace(Replace(title,'\t',''),'\r',''), ' ', ''))" => trim(String::stripAllWhiteSpaces($title, '')),
                    SqlBuilder::orWhere(array('release_date' => $release_date, 're_release_date' => $release_date))
                )
            );
            if (empty($local_movie)) {
                $local_movie = $movie_model->getRow(array('title' => $title));
                if (empty($local_movie)) {
                    $local_movie = $movie_model->getRow(
                        array(
                            "TRIM(replace(Replace(Replace(title,'\t',''),'\r',''), ' ', ''))" => trim(String::stripAllWhiteSpaces($title, ''))
                        )
                    );
                    if (empty($local_movie)) {
                        Log::warning('매칭되는 영화 없음', array('lotte_title' => $title, 'lotte_release_date' => $release_date));

                        return false;
                    } else {
                        Log::warning('영화 제목만으로 매핑(strip white space)', array($movie, $local_movie));
                    }
                } else {
                    Log::warning('영화 제목만으로 매핑', array($movie, $local_movie));
                }
            }
        }

        $ret = $movie_model->modify(array('lotte_id' => $movie['contentCode']), array('movie_id' => $local_movie['movie_id']));
        if (!$ret) {
            Log::error('롯데 아이디 업데이트 실패', array('lotte_id' => $movie['contentCode'], 'movie_id' => $local_movie['movie_id']));

            return false;
        }

        Log::info('롯데 아이디 업데이트 성공', array('movie_id' => $local_movie['movie_id'], 'lotte_id' => $movie['contentCode'], 'title' => $local_movie['title']));

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

        if (empty($content['lotte_id'])) {
            Log::error('LOTTE ID 없음', compact('content_id'));

            return false;
        }

        $content_tree = null;
        $content_html = $this->crawler->getContent($content['lotte_id'], $content_tree);
        if (empty($content_html) || empty($content_tree)) {
            Log::error('Content lotte에서 조회 실패', compact('content_id'));

            return false;
        }

        $score_box_tree = $content_tree->find('.movie_detail_grade');
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

        $movie_model->begin();
        $ret = $movie_model->modify(array('lotte_grade_point' => $rates['point'], 'lotte_grade_count' => $rates['count']), array('movie_id' => $content_id));
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
                'grade_vendor' => CONTENTS_PROVIDER_LOTTE,
                'insert_date'  => $insert_date
            );

            if (!$grade_model->exist($grade_where)) {
                $grade_data = array(
                    'content_id'   => $content_id,
                    'grade_vendor' => CONTENTS_PROVIDER_LOTTE,
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

        Log::info('롯데 평점 동기화 성공', compact('content_id'));
        $movie_model->commit();

        return true;
    }
}