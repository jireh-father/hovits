<?php
namespace middleware\service\contents\sync;

use framework\library\File;
use framework\library\Log;
use framework\library\sql_builder\SqlBuilder;
use framework\library\Time;
use middleware\model\ContentSyncLog;
use middleware\model\Movie;
use middleware\model\RealtimeBoxoffice;
use middleware\model\ContentGrade;

class WatchaMovieSync extends WatchaSync
{
    public function __construct()
    {
        parent::__construct(CONTENT_TYPE_MOVIE);
    }

    public function syncContentId($search_html, $content, $is_force = true)
    {
        if ($this->isMovieContent()) {
            if (!empty($content['cgv_id']) && $is_force === false) {
                return true;
            }
        }

        $this->parser->content_id = $content['movie_id'];

        $cgv_id = $this->parser->extractContentIdInSearch($search_html, $content);

        if (empty($cgv_id)) {
            return false;
        }

        $sync_model = ContentSyncLog::getInstance();
        $sync_model->add(
            array(
                'content_provider'    => CONTENTS_PROVIDER_WATCHA,
                'content_id'          => $content['movie_id'],
                'provider_content_id' => $cgv_id,
                'sync_type'           => 'CONTENT_ID_MAPPING'
            )
        );

        $model = Movie::getInstance();

        return $model->modify(array('cgv_id' => $cgv_id), array('movie_id' => $content['movie_id']));
    }

    public function syncContentIdDirect($content_id, $is_force = true)
    {
        if (empty($content_id)) {

            return false;
        }

        $movie_model = Movie::getInstance();
        if ($is_force === false && $movie_model->exist(array('movie_id' => $content_id, SqlBuilder::orWhere(array(SqlBuilder::isNotNull('cgv_id'), 'cgv_disabled' => true))))) {
            return false;
        }

        $content = $movie_model->getRow(array('movie_id' => $content_id));
        if (empty($content)) {
            Log::error('Content DB 검색 실패', compact('content_id'));

            return false;
        }

        $search_html = $this->crawler->getSearchPage($content['title'], $content_id);
        if (empty($search_html) === true) {
            Log::error('Content 내용물 없음', compact('content_id'));

            return false;
        }

        if (empty($content)) {
            Log::error('Content DB 검색 실패', compact('content_id'));

            return false;
        }

        return $this->syncContentId($search_html, $content, $is_force);
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

        if (empty($content['cgv_id'])) {
            Log::error('CGV ID 없음', compact('content_id'));

            return false;
        }

        $content_html = $this->crawler->getContent($content['cgv_id'], $content_tree);
        if (empty($content_html)) {
            Log::error('Content cgv에서 조회 실패', compact('content_id'));

            return false;
        }

        $rates = $this->parser->extractRateInMain($this->parser->getSearchTopElement($content_tree));

        if (empty($rates)) {
            Log::error('평점 추출 실패', compact('content_id'));

            return false;
        }

        if (empty($rates['point']) && empty($rates['count'])) {
            Log::error('평점 없음', compact('content_id'));

            return false;
        }

        $rates['point'] = $rates['point'] * 10;

        $grade_model = ContentGrade::getInstance();
        $movie_model->begin();
        $ret = $movie_model->modify(array('cgv_grade_point' => $rates['point'], 'cgv_grade_count' => $rates['count']), array('movie_id' => $content_id));
        if (!$ret) {
            Log::error('영화 테이블에 rate 업데이트 실패', compact('content_id'));
            $movie_model->rollBack();

            return false;
        }

        if ($update_history) {
            $grade_data = array('content_id' => $content_id, 'grade_vendor' => CONTENTS_PROVIDER_CGV, 'grade_point' => $rates['point'], 'grade_count' => $rates['count']);

            $ret = $grade_model->add($grade_data);

            if (!$ret) {
                Log::error('유저 평가 테이블에 업데이트 실패', compact('content_id'));
                $movie_model->rollBack();

                return false;
            }
        }

        Log::info('CGV 평점 업데이트 성공', $content_id);

        $movie_model->commit();

        return true;
    }

    public function syncAllMovieRate()
    {
        $movie_model = Movie::getInstance();
        $movies = $movie_model->getList(array(SqlBuilder::isNotNull('cgv_id')));

        if (empty($movies)) {
            Log::error('cgv id 있는 영화 데이터 없음');
        }

        Log::info('CGV 영화 평점 동기화 시작', count($movies));

        $results = array(0, 0);
        foreach ($movies as $movie) {
            $ret = $this->syncMovieRate($movie['movie_id']);
            if ($ret) {
                $results[0]++;
            } else {
                $results[1]++;
            }
        }

        return $results;
    }
}