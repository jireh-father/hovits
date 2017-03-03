<?php
namespace middleware\service\contents\sync;

use framework\library\File;
use framework\library\Log;
use framework\library\sql_builder\SqlBuilder;
use framework\library\Time;
use middleware\model\ContentGrade;
use middleware\model\ContentSyncLog;
use middleware\model\Movie;
use middleware\model\RealtimeBoxoffice;

class NaverMovieSync extends NaverSync
{
    public function __construct()
    {
        parent::__construct(CONTENT_TYPE_MOVIE);
    }

    public function syncContentId($search_json, $content, $is_force = true)
    {
        if ($this->isMovieContent()) {
            if (!empty($content['naver_id']) && $is_force === false) {
                return true;
            }
        }

        $this->parser->content_id = $content['movie_id'];

        $search_movie = $this->parser->parseMovieJson($search_json);

        foreach ($search_movie as $naver_movie) {
            $is_same = $this->checkSameMovie($naver_movie, $content);

            if (!$is_same) {
                Log::warning('영화 비교 다름', array($content['movie_id'], $naver_movie));

                continue;
            }

            $naver_id = $naver_movie['naver_id'];

            if (empty($naver_id)) {
                Log::warning('네이버 링크통해서 영화아이디 얻어오기 실패', array($content['movie_id'], $naver_movie));

                continue;
            }

            $sync_model = ContentSyncLog::getInstance();
            $sync_model->add(
                array(
                    'content_provider'    => CONTENTS_PROVIDER_NAVER,
                    'content_id'          => $content['movie_id'],
                    'provider_content_id' => $naver_id,
                    'sync_type'           => 'CONTENT_ID_MAPPING'
                )
            );

            $model = Movie::getInstance();

            return $model->modify(array('naver_id' => $naver_id), array('movie_id' => $content['movie_id']));
        }

        return false;
    }

    public function checkSameMovie($search_movie, $content)
    {
        $content_id = $content['movie_id'];
        // 제목 띄어쓰기 없애서 비교
        $search_title = $this->crawler->buildSearchKeyword($search_movie['title']);
        $movie_title = $this->crawler->buildSearchKeyword($content['title']);
        $has_making_year = false;
        $has_dircetor = false;
        $has_actor = false;
        $is_making_year_same = false;
        $is_director_same = false;
        $is_actor_same = false;
        if ($search_title !== $movie_title) {
            Log::info('제목 다름', array($search_title, $movie_title, $content_id));

            return false;
        } else {
            Log::info('제목 같음', array($search_title, $movie_title, $content_id));
        }

        //제작년
        if (!empty($search_movie['pubDate']) && !empty($content['making_year'])) {
            $has_making_year = true;
            if ($search_movie['pubDate'] != $content['making_year']) {
                Log::info('제작년도 다름', array($search_movie['pubDate'], $content['making_year'], $content_id));
                if (abs($search_movie['pubDate'] - $content['making_year']) <= 2) {
                    Log::info('제작년도 2년차이라 통과함', array($search_movie['pubDate'], $content['making_year'], $content_id));
                    $is_making_year_same = true;
                }
            } else {
                $is_making_year_same = true;
                Log::info('제작년도 같음', array($search_movie['pubDate'], $content['making_year'], $content_id));
            }
        }

        //감독(하나라도)
        if (!empty($search_movie['director']) && !empty($content['directors'])) {
            $has_dircetor = true;
            $local_directors = json_decode($content['directors'], true);
            $diff_directors = array_diff($local_directors, $search_movie['director']);
            if (count($local_directors) === count($diff_directors)) {
                Log::info('감독 아예 다름', array($local_directors, $search_movie['director'], $content_id));
            } else {
                $is_director_same = true;
                Log::info('감독 같은거 있음', array($local_directors, $search_movie['director'], $content_id));
            }
        }

        //배우(50프로이상)
        if (!empty($search_movie['actor']) && !empty($content['lead_actors'])) {
            $has_actor = true;
            $actors = json_decode($content['lead_actors'], true);
            $diff_actors = array_diff($actors, $search_movie['actor']);
            $actor_cnt = count($actors);
            $same_cnt = $actor_cnt - count($diff_actors);
            $same_percent = $same_cnt / $actor_cnt * 100;
            if ($same_percent < 40) {
                Log::info('배우 같은게 40프로 미만임', array($actors, $search_movie['actor'], $content_id));
            } else {
                $is_actor_same = true;
                Log::info('배우 같은게 40프로 이상임', array($actors, $search_movie['actor'], $content_id));
            }
        }

        if ($is_making_year_same && $is_director_same && $is_actor_same) {
            return true;
        } elseif (!$has_making_year && $is_director_same && $is_actor_same) {
            return true;
        } else {
            $html = $this->crawler->getContent($search_movie['naver_id'], $content_tree);
            if (empty($html)) {
                Log::warning('상세정보 못얻어옴', array($search_movie['naver_id'], $content_id));

                return false;
            }

            $info_box = $this->parser->extractInfoBox($content_tree);
            if (empty($info_box)) {
                Log::warning('info box 못찾음', array($search_movie['naver_id'], $content_id));

                return false;
            }
            $release_date = $this->parser->extractReleaseDate($info_box);
            if (empty($release_date)) {
                Log::warning('개봉일 못찾음(없음)', array($search_movie['naver_id'], $content_id));

                if (($is_making_year_same && $is_director_same) || ($is_actor_same && $is_director_same)
                    || ($is_actor_same && $is_making_year_same) || ($is_actor_same && $is_making_year_same && $is_director_same)
                ) {
                    Log::warning('개봉일 빼고 다 같거나 2개 이상 같음', array($search_movie['naver_id'], $content_id));

                    return true;
                }

                if ($is_director_same || $is_actor_same) {
                    Log::warning('감독이나 배우만 같음', array($search_movie['naver_id'], $content_id));

                    return true;
                }

                return false;
            }
            $is_release_date_same = false;
            if ($release_date === $content['release_date'] || $release_date === $content['re_release_date']) {
                Log::warning('개봉일 같음!!', array($search_movie['naver_id'], $content_id));
                $is_release_date_same = true;
            }

            if ($is_release_date_same) {
                if ($is_director_same || $is_actor_same) {
                    Log::warning('개봉일 같은데 감독이나 배우 같아서 통과!!', array($search_movie['naver_id'], $content_id));

                    return true;
                }
            }
        }

        return false;
    }

    public function syncContentIdDirect($content_id, $is_force = true)
    {
        if (empty($content_id)) {

            return false;
        }

        $movie_model = Movie::getInstance();
        if ($is_force === false && $movie_model->exist(array('movie_id' => $content_id, SqlBuilder::orWhere(array(SqlBuilder::isNotNull('naver_id'), 'naver_disabled' => true))))) {
            return false;
        }

        $content = $movie_model->getRow(array('movie_id' => $content_id));
        if (empty($content)) {
            Log::error('Content DB 검색 실패', compact('content_id'));

            return false;
        }

        $search_array = $this->crawler->getSearchPage($content['title'], $content_id);
        if (empty($search_array) === true) {
            Log::error('Content 내용물 없음', compact('content_id'));

            return false;
        }

        $this->crawler->setNaverMovieId($search_array, $content_id);

        return $this->syncContentId($search_array, $content, $is_force);
    }

    public function syncContentIdsDirect(array $content_ids, $is_force = true)
    {
        if (empty($content_ids)) {
            return false;
        }

        $result = array(0, 0);
        foreach ($content_ids as $content_id) {
            $ret = $this->syncContentIdDirect($content_id, $is_force);
            if ($ret) {
                $result[0]++;
            } else {
                $result[1]++;
            }
        }

        return $result;
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

        if (empty($content['naver_id'])) {
            Log::error('NAVER ID 없음', compact('content_id'));

            return false;
        }

        $content_html = $this->crawler->getContent($content['naver_id'], $content_tree);
        if (empty($content_html)) {
            Log::error('Content naver에서 조회 실패', compact('content_id'));

            return false;
        }

        $score_box_tree = $content_tree->find('.mv_info_area .main_score');
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

        $rates['point'] = round($rates['point'] * 10);

        $grade_model = ContentGrade::getInstance();
        $movie_model->begin();
        $ret = $movie_model->modify(array('naver_grade_point' => $rates['point'], 'naver_grade_count' => $rates['count']), array('movie_id' => $content_id));
        if (!$ret) {
            Log::error('영화 테이블에 rate 업데이트 실패', compact('content_id'));
            $movie_model->rollBack();

            return false;
        }

        if ($update_history) {
            $insert_date = Time::Ymd();
            $grade_where = array(
                'content_id'   => $content_id,
                'grade_vendor' => CONTENTS_PROVIDER_NAVER,
                'insert_date'  => $insert_date
            );

            if (!$grade_model->exist($grade_where)) {
                $grade_data = array(
                    'content_id'   => $content_id,
                    'grade_vendor' => CONTENTS_PROVIDER_NAVER,
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

        Log::info('네이버 평점 동기화 성공', compact('content_id'));
        $movie_model->commit();

        return true;
    }
}