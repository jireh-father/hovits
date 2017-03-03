<?php
namespace middleware\service\contents\crawler;

use framework\library\File;
use framework\library\Log;
use framework\library\sql_builder\SqlBuilder;
use framework\library\Time;
use middleware\library\Curl;
use middleware\library\QpWrapper;
use middleware\model\Movie;

class CgvMovieCrawler extends CgvCrawler
{
    public function __construct()
    {
        parent::__construct(CONTENT_TYPE_MOVIE);
    }

    public function buildSearchKeyword($search_keyword)
    {
        $search_keyword = preg_replace('/\s/', '', $search_keyword);
        $search_keyword = preg_replace('/[!@#$%^*=_+`\\\|>,<\'";\[{}\]]/', ' ', $search_keyword);
        $search_keyword = trim($search_keyword);

        return rawurlencode($search_keyword);
    }

    public function stripTitle($title)
    {
        $title = preg_replace('/[!@#$%^&*()=_+`~\\\|\/?.>,<\'";:\[{}\]-]/', ' ', $title);
        $title = preg_replace('/\s/', '', $title);

        return trim($title);
    }

    public function crawlSearchPage($content_id, $titles = null, $is_force = true)
    {
        if (empty($content_id)) {
            Log::error('파라미터 에러', $content_id);

            return false;
        }

        $path = $this->getSearchPagePath($content_id);

        if (is_file($path) && !$is_force) {
            return true;
        }

        if (empty($titles)) {
            $model = Movie::getInstance();
            $movie = $model->getRow(array('movie_id' => $content_id));
            $titles = $this->getTitles($movie);
            if (empty($titles)) {
                Log::error('영화 검색 실패', $content_id);

                return false;
            }
        }

        $search_html = $this->getSearchPage($titles, $content_id, $search_tree);

        if ($search_html === -1) {

            return -1;
        }

        if (empty($search_html)) {

            return false;
        }

        if (empty($search_tree)) {
            Log::debug('QpWrapper html 파싱 실패', array($titles, $content_id, $search_html));

            return false;
        }

        $ret = File::writeToFile($path, $search_html);

        if (!$ret) {
            Log::error('검색페이지 저장 실패', array($path, $search_html));

            return false;
        }

        $this->search_crawled_cnt++;

        Log::info('검색결과 크롤링 성공', array($content_id, $titles, $path, $this->search_crawled_cnt));

        return true;
    }

    public function getSearchPageById($content_id)
    {
        if (empty($content_id)) {
            return null;
        }
        $model = Movie::getInstance();
        $movie = $model->getRow(array('movie_id' => $content_id));
        $titles = $this->getTitles($movie);
        if (empty($titles)) {
            Log::error('영화 제목검색 실패', $content_id);

            return null;
        }

        return $this->getSearchPage($titles, $content_id);
    }

    public function getSearchPage($search_keywords, $content_id = null, &$search_tree = null)
    {
        if (empty($search_keywords)) {
            Log::error('검색어 없음', $content_id);

            return null;
        }

        if (is_array($search_keywords)) {
            $search_keyword = $search_keywords[0];
        } else {
            $search_keyword = $search_keywords;
        }

        $encode_search_keyword = $this->buildSearchKeyword($search_keyword);

        $api_url = $this->buildCommonSearchUrl($encode_search_keyword);

        Log::info('검색시작', array($api_url, $content_id, $search_keyword, $search_keywords));

        try {
            $search_html = Curl::get($api_url, null, 3, null, 15, 7);
        } catch (\Exception $e) {
            Log::error('curl 실패', array($api_url, $content_id, $search_keyword));

            return -1;
        }

        if (empty($search_html)) {
            Log::error('검색 실패(내용 비었음)', array($api_url, $content_id));

            return -1;
        }

        $search_tree = QpWrapper::getInstance($search_html);

        if (empty($search_tree) === true) {
            Log::error('cgv 검색결과 트리 생성 실패', $content_id);

            return null;
        }

        $title_element = $search_tree->find('html title');
        $has_movie_info = true;
        if ($title_element->exists() && $title_element->text() === 'Object moved') {
            Log::error('검색 결과가 없습니다1.', array($api_url, $content_id));
            $has_movie_info = false;
        }

        if ($search_tree->find('.sect-noresult')->exists()) {
            Log::error('검색 결과가 없습니다2.', array($api_url, $content_id));
            $has_movie_info = false;
        }

        if (!$this->parser->getSearchTopElement($search_tree) && !$this->parser->getSearchListElement($search_tree)) {
            Log::error('검색결과에 영화정보 없음', array($api_url, $content_id));
            $has_movie_info = false;
        }

        if (!$has_movie_info) {
            if (is_array($search_keywords) && count($search_keywords) > 1) {
                array_shift($search_keywords);
                Log::info('다른 제목으로 재시도', array($content_id, $search_keywords[0], $search_keywords));

                return $this->getSearchPage($search_keywords, $content_id, $search_tree);
            } else {
                return null;
            }
        }

        return $search_html;
    }

    /**
     * @param string $search_keywords
     * @param string $content_id
     * @param integer|null $page
     * @param QpWrapper $search_tree
     * @return null|void
     */
    public function getMovieSearchPage($search_keywords, $content_id = null, $page = null, &$search_tree = null)
    {
        if (empty($search_keywords)) {
            Log::error('검색어 없음', $content_id);

            return null;
        }

        if (is_array($search_keywords)) {
            $search_keyword = $search_keywords[0];
        } else {
            $search_keyword = $search_keywords;
        }

        $encoded_search_keyword = $this->buildSearchKeyword($search_keyword);
        $api_url = $this->buildMovieSearchUrl($encoded_search_keyword, $page);

        Log::info('영화 검색시작', array($api_url, $content_id, $search_keyword));

        try {
            $search_html = Curl::get($api_url, null, 3, null, 15, 7);
        } catch (\Exception $e) {
            Log::error('curl 실패', array($api_url, $content_id, $search_keyword));

            return false;
        }

        if (empty($search_html)) {
            Log::error('검색 실패(내용 비었음)', array($api_url, $content_id));

            return false;
        }

        $search_tree = QpWrapper::getInstance($search_html);

        if (empty($search_tree) === true) {
            Log::error('cgv 검색결과 트리 생성 실패', $content_id);

            return null;
        }

        $has_movie_info = true;
        $title_element = $search_tree->find('html title');
        if ($title_element->exists() && $title_element->text() === 'Object moved') {
            Log::error('검색 결과가 없습니다.', array($api_url, $content_id));
            $has_movie_info = false;
        }

        if ($search_tree->find('.sect-noresult')->exists()) {
            Log::error('검색 결과가 없습니다.', array($api_url, $content_id));
            $has_movie_info = false;
        }

        if (!$has_movie_info) {
            if (is_array($search_keywords) && count($search_keywords) > 1) {
                array_shift($search_keywords);
                Log::info('다른 제목으로 재시도', array($content_id, $search_keywords[0]));

                return $this->getMovieSearchPage($search_keywords, $content_id, $page, $search_tree);
            } else {
                return null;
            }
        }

        return $search_html;
    }

    public function crawlSearchPageList($movies, $is_force = true)
    {
        if (empty($movies) === true) {
            return false;
        }

        $result = array(0, 0);
        foreach ($movies as $i => $movie) {
            $content_id = $movie['movie_id'];
            $path = $this->getSearchPagePath($content_id);
            if (is_file($path) && !$is_force) {
                Log::info('파일 이미 존재해서 크롤링 통과', $content_id);
                continue;
            }

            $titles = $this->getTitles($movie);
            Log::info('검색 크롤링 시작', array($content_id, $titles));
            $ret = $this->crawlSearchPage($content_id, $titles, $is_force);
            if ($ret === true) {
                $result[0]++;
            } else {
                $result[1]++;
                if (empty($movie)) {
                    Log::warning('영화검색 실패', $content_id);
                } else {
                    if ($ret !== -1) {
                        $this->disableMovieMapping($movie);
                    }
                }
            }
            $this->_sleep(200, 500);
        }

        return $result;
    }

    public function crawlEmptySearchPages()
    {
        $model = Movie::getInstance();
        $movies = $model->getList(
            array(
                SqlBuilder::isNull('cgv_id'),
                'cgv_disabled' => false,
                SqlBuilder::orWhere(
                    array(
                        SqlBuilder::dateRange('release_date', self::CGV_ESTABLISH_DATE),
                        SqlBuilder::dateRange('re_release_date', self::CGV_ESTABLISH_DATE)
                    )
                )
            )
        );

        $cnt = count($movies);
        Log::info('cgv 검색 크롤링 시작', array('total' => $cnt));

        $result = $this->crawlSearchPageList($movies);

        Log::info('cgv 검색 크롤링 종료', array('total' => $cnt, 'result' => $result));

        return $result;
    }
}