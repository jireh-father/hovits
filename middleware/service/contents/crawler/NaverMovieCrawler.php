<?php
namespace middleware\service\contents\crawler;

use framework\library\File;
use framework\library\FileCache;
use framework\library\Log;
use framework\library\sql_builder\SqlBuilder;
use framework\library\String;
use middleware\exception\CrawlerException;
use middleware\library\Curl;
use middleware\library\QpWrapper;
use middleware\model\Movie;

class NaverMovieCrawler extends NaverCrawler
{
    const MAX_CRAWLING_MOVIE_CNT = 10000;

    public function __construct()
    {
        parent::__construct(CONTENT_TYPE_MOVIE);
    }

    public function buildSearchKeyword($search_keyword)
    {
        $search_keyword = preg_replace('/\s/', '', $search_keyword);
        //        $search_keyword = preg_replace('/[!@#$%^*=_+`\\\|>,<\'";\[{}\]]/', ' ', $search_keyword);
        $search_keyword = trim($search_keyword);

        return $search_keyword;
    }

    public function stripTitle($title)
    {
        $title = preg_replace('/[!@#$%^&*()=_+`~\\\|\/?.>,<\'";:\[{}\]-]/', ' ', $title);
        $title = preg_replace('/\s/', '', $title);

        return trim($title);
    }

    public function crawlSearchPageById($content_id, $is_force = true)
    {
        if (empty($content_id)) {
            return false;
        }

        $movie_model = Movie::getInstance();
        $movie = $movie_model->getRow(array('movie_id' => $content_id));
        if (empty($movie)) {
            return false;
        }

        return $this->crawlSearchPage($movie, $is_force);
    }

    public function crawlSearchPage($movie, $is_force = true)
    {
        if (empty($movie)) {
            Log::error('파라미터 에러', $movie);

            return false;
        }

        $content_id = $movie['movie_id'];

        $path = $this->getSearchPagePath($content_id);

        if (is_file($path) && !$is_force) {
            return true;
        }

        $titles = $this->getTitles($movie);
        if (empty($titles)) {
            Log::error('영화 검색 실패', $content_id);

            return false;
        }
        $search_array = $this->getSearchPage($titles, $content_id);

        if ($search_array === -1) {

            return -1;
        }

        if (empty($search_array)) {

            return false;
        }

        $this->setNaverMovieId($search_array, $content_id);

        $ret = File::writeToFile($path, json_encode($search_array));

        if (!$ret) {
            Log::error('검색페이지 저장 실패', array($path, $search_array));

            return false;
        }

        $this->search_crawled_cnt++;

        Log::info('검색결과 크롤링 성공', array($content_id, $titles, $path, $this->search_crawled_cnt));

        return true;
    }

    public function setNaverMovieId(&$search_array, $content_id)
    {
        foreach ($search_array as &$search_item) {
            if (empty($search_item['link'])) {
                Log::error('네이버 리스트에 link 데이터 없음', array($content_id, $search_item));
                continue;
            }

            $cache_key = $this->getNaverMovieCacheKey($search_item);
            if (empty($cache_key)) {
                Log::error('네이버 cache key 생성 실패', array($content_id, $search_item));
                continue;
            }

            $naver_id_cache = $this->getNaverIdCache($cache_key);

            if (empty($naver_id_cache)) {
                $naver_id = $this->parser->extractContentIdByLink($search_item['link']);
                if (empty($naver_id)) {
                    Log::error('네이버 상세에서 naver id 뽑아오기 실패', array($content_id, $search_item));
                    continue;
                }
                $this->setNaverIdCache($cache_key, $naver_id);
            } else {
                $naver_id = $naver_id_cache;
            }
            unset($search_item['link']);
            $search_item['naver_id'] = $naver_id;
        }
    }

    public function getNaverMovieCacheKey($naver_movie)
    {
        if (empty($naver_movie)) {
            return null;
        }
        $hash = md5(implode('_', array($naver_movie['title'], $naver_movie['subtitle'], $naver_movie['pubDate'], $naver_movie['director'], $naver_movie['actor'])));

        return 'naver_movie_id_cache/' . substr($hash, 0, 2) . '/' . substr($hash, 2, 3) . '/' . $hash;
    }

    public function getNaverIdCache($cache_key)
    {
        return FileCache::get($cache_key);
    }

    public function setNaverIdCache($cache_key, $naver_id)
    {
        return FileCache::set($cache_key, $naver_id);
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

        return $this->getSearchPage($titles, $content_id, $this->getMakingCountries($movie));
    }

    /**
     * array(8) {
     * ["title"]=>
     * string(36) "Naver Open API - movie ::'베테랑'"
     * ["link"]=>
     * string(23) "http://search.naver.com"
     * ["description"]=>
     * string(19) "Naver Search Result"
     * ["lastBuildDate"]=>
     * string(31) "Tue, 01 Sep 2015 17:56:30 +0900"
     * ["total"]=>
     * string(1) "1"
     * ["start"]=>
     * string(1) "1"
     * ["display"]=>
     * string(1) "1"
     * ["item"]=>
     *      array(8) {
     *              ["title"]=>
     *              string(16) "<b>베테랑</b>"
     *              ["link"]=>
     *              string(273) "http://openapi.naver.com/l?AAADWLywqCQBSGn+bMUuaijLOYhaVBC5EgiNzN6JmU8pJZoE/fGAQ//w2+5xunRUO2gx2HhEKWQhxBvN8elYFiZF5G1N3waZHccdGccya5pSx2ijoR185IhZZb41Aai6SZ0OlmnkcQCfCD1w8OevPBKaiG7v/4tO02NrPm1VZB3/QgDtVQI4iUsUhJSWbNwpBRXxVllJNOl7fzWovotFYvTz4ujywprgU9FnmZUw9+AdWjDELUAAAA"
     *              ["image"]=>
     *              string(63) "http://imgmovie.naver.com/mdi/mit110/1159/115977_P11_102909.jpg"
     *              ["subtitle"]=>
     *              string(7) "Veteran"
     *              ["pubDate"]=>
     *              string(4) "2015"
     *              ["director"]=>
     *              string(10) "류승완|"
     *              ["actor"]=>
     *              string(40) "황정민|유아인|유해진|오달수|"
     *              ["userRating"]=>
     *              string(4) "9.09"
     *      }
     * }
     *
     * @param $search_keywords
     * @param null $content_id
     * @param null $making_country
     * @throws CrawlerException
     */
    public function getSearchPage($search_keywords, $content_id = null, $making_country = null)
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

        if (empty($this->api_key)) {
            $this->api_key = self::$api_keys[$this->cur_api_key_idx];
        }

        $encode_search_keyword = $this->buildSearchKeyword($search_keyword);

        $api_url = $this->buildCommonSearchUrl($encode_search_keyword, $making_country, $this->api_key);
        if (empty($api_url)) {
            Log::error('url 빌드 실패', $content_id);

            return null;
        }

        Log::info('검색시작', array($api_url, $content_id, $search_keyword, $search_keywords));

        try {
            $search_html = Curl::get($api_url, null, 3);
        } catch (\Exception $e) {
            Log::error('curl 실패', array($api_url, $content_id, $search_keyword));

            return -1;
        }

        if (empty($search_html)) {
            Log::error('검색 실패(내용 비었음)', array($api_url, $content_id));

            return -1;
        }

        if (String::has($search_html, 'Your query request count is over the limit')) {
            Log::error('api만료', array($api_url, $content_id));
            if (isset(self::$api_keys[$this->cur_api_key_idx + 1])) {
                $this->api_key = self::$api_keys[$this->cur_api_key_idx + 1];
                $this->cur_api_key_idx++;

                return $this->getSearchPage($search_keywords, $content_id, $making_country);
            } else {
                throw new CrawlerException('api key 만료');
            }
        }

        $search_array = json_decode(json_encode(simplexml_load_string($search_html)), true);
        if (empty($search_array) || empty($search_array['channel'])) {
            Log::error('검색 데이터 이상', array($api_url, $content_id, $search_array, $search_html));

            return -1;
        }

        if ($search_array['channel']['total'] < 1) {

            Log::error('검색 데이터 0건', array($api_url, $content_id, $search_array, $search_html));

            return null;
        }


        if ($search_array['channel']['total'] == 1) {
            $search_array['channel']['item'] = array($search_array['channel']['item']);
        }

        if (empty($search_array['channel']['item'][0])) {
            return array($search_array['channel']['item']);
        } else {
            return $search_array['channel']['item'];
        }
    }

    public function getMoviePageByLink($link, &$movie_tree = null)
    {
        if (empty($link)) {
            return null;
        }

        $html = Curl::get($link, null, 3, null, 30, 5, array(CURLOPT_FOLLOWLOCATION => true));

        if (empty($html)) {
            Log::error('get 결과 비어있음', $link);

            return -1;
        }

        $movie_tree = QpWrapper::getInstance($html);
        $title = $movie_tree->find('[property="og:title"]');
        if (!$title->exists()) {
            Log::error('영화 상세정보 아닌듯', array($link, $html));

            return null;
        }

        return $html;
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
            try {
                $ret = $this->crawlSearchPage($movie, $is_force);
            } catch (\Exception $e) {
                Log::error('크롤링 실패 예외발생', array($content_id, $e));
                $ret = -1;
            }
            if ($ret === true) {
                $result[0]++;
            } else {
                $result[1]++;
                Log::error('크롤링 실패', $content_id);
                if ($ret !== -1) {
                    $this->disableMovieMapping($movie);
                }
            }
        }

        return $result;
    }

    public function crawlEmptySearchPages($is_force = true)
    {
        $model = Movie::getInstance();
        $where = array(
            SqlBuilder::isNull('naver_id'),
            'naver_disabled' => false
        );
        $cnt = $model->getRowCount($where);

        if ($cnt < 1) {
            Log::info('처리할 영화가 없습니다.');

            return false;
        }

        $offset = 0;

        $movies = $model->getList(
            array(
                SqlBuilder::isNull('naver_id'),
                'naver_disabled' => false
            ),
            null,
            $offset . ',' . self::MAX_CRAWLING_MOVIE_CNT
        );

        $results = array();

        while (!empty($movies)) {
            Log::info('naver 검색 크롤링 시작', array('total' => $cnt, 'offset' => $offset));

            $result = $this->_crawlEmptySearchPages($movies, $is_force);
            $results = array_merge($results, $result);

            Log::info('naver 검색 크롤링 종료', array('total' => $cnt, 'offset' => $offset, 'result' => $result));

            $offset += self::MAX_CRAWLING_MOVIE_CNT;

            $movies = $model->getList(
                array(
                    SqlBuilder::isNull('naver_id'),
                    'naver_disabled' => false
                ),
                null,
                $offset . ',' . self::MAX_CRAWLING_MOVIE_CNT
            );
        }

        return $results;
    }

    private function _crawlEmptySearchPages($movies, $is_force = true)
    {
        return $this->crawlSearchPageList($movies, $is_force);
    }
}