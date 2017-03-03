<?php
namespace middleware\service\contents\crawler;

use framework\library\Log;
use framework\library\String;
use middleware\library\Constant;
use middleware\library\Curl;
use middleware\library\QpWrapper;
use middleware\service\contents\NaverContents;
use middleware\service\contents\parser\NaverMovieParser;

abstract class NaverCrawler extends NaverContents
{
    const SEARCH_COMMON_API = 'http://openapi.naver.com/search?display=100&start=1&target=movie&';
    const SEARCH_MOVIE_API = 'http://www.naver.co.kr/search/movie.aspx?query=';
    const CONTENT_API_MOVIE = 'http://movie.naver.com/movie/bi/mi/point.nhn?code=';

    public $parser;

    protected static $api_keys = array(
        '222172b018f90f38dfa79eb2bafe7abe',
        'f2e96ed6fbd76442fa671fe531917130',
        '850705c77abf3bee20fa9cc039896e9a'
    );

    protected $api_key;

    protected $cur_api_key_idx = 0;

    protected $search_crawled_cnt = 0;

    public function __construct($content_type)
    {
        parent::__construct($content_type);

        if ($this->isMovieContent()) {
            $this->parser = new NaverMovieParser($this);
        } else {
        }
    }

    public function buildCommonSearchUrl($search_keyword, $making_country, $api_key)
    {
        if (empty($search_keyword) || empty($api_key)) {
            return null;
        }
        $country_code_query = '';
        if (!empty($making_country)) {
            $country_code_query = '&country=' . Constant::$country_code_map[$making_country];
        }

        return self::SEARCH_COMMON_API . "key={$api_key}&query={$search_keyword}{$country_code_query}";
    }

    public function buildMovieSearchUrl($search_keyword, $page = null)
    {
        $url = self::SEARCH_MOVIE_API . $search_keyword;
        if (!empty($page)) {
            $url .= "&page={$page}";
        }

        return $url;
    }

    public function crawlSearchPage($content_id, $titles = null)
    {
    }

    public function getSearchPageById($content_id)
    {
    }

    public function getSearchPage($search_keywords, $content_id = null)
    {
    }

    public function crawlSearchPageList(array $content_id_list, array $title_list = null)
    {
    }

    public function crawlEmptySearchPages()
    {
    }

    /**
     * @param $search_keyword
     * @param $no_space
     * @return string
     */
    public function buildSearchKeyword($search_keyword, $no_space = false)
    {
    }

    /**
     * @param string $search_keyword
     * @param string $content_id
     * @param integer|null $page
     * @param QpWrapper $search_tree
     * @return null|void
     */
    public function getMovieSearchPage($search_keyword, $content_id = null, $page = null, &$search_tree = null)
    {
    }

    public function buildContentUrl($content_id)
    {
        if (empty($content_id)) {
            return null;
        }

        if ($this->isMovieContent()) {
            return self::CONTENT_API_MOVIE . $content_id;
        }
    }

    public function getContent($content_id, &$content_tree = null)
    {
        if (empty($content_id)) {
            return null;
        }

        $api_url = $this->buildContentUrl($content_id);

        try {
            $content_html = Curl::get($api_url, null, 3, null, 15, 7);
        } catch (\Exception $e) {
            Log::error('curl 실패', array($api_url, $content_id));

            return null;
        }

        if (empty($content_html)) {
            Log::error('검색 실패(내용 비었음)', $content_id);

            return null;
        }

        $content_tree = QpWrapper::getInstance($content_html);
        $content_tree->onAutoDecodeUtf8();

        if (empty($content_tree) === true) {
            Log::error('naver 검색결과 트리 생성 실패', $content_id);

            return null;
        }

        $container_element = $content_tree->find('#container');
        if ($container_element->exists() && String::has($container_element->html(), '영화 코드값 오류')) {
            Log::error('영화 코드값 오류.', array($api_url, $content_id));

            return null;
        }

        if (!$content_tree->find('meta[property="og:title"]')->exists()) {
            Log::error('영화 상세 페이지가 이상합니다.', array($api_url, $content_id));

            return null;
        }

        $content_tree->offAutoDecodeUtf8();

        return $content_html;
    }

    public function getMoviePageByLink($link, &$movie_tree = null)
    {
        return null;
    }
}