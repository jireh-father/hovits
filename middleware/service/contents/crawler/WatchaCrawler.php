<?php
namespace middleware\service\contents\crawler;

use framework\library\Log;
use middleware\library\Curl;
use middleware\library\QpWrapper;
use middleware\service\contents\CgvContents;
use middleware\service\contents\parser\CgvMovieParser;
use middleware\service\contents\parser\WatchaMovieParser;
use middleware\service\contents\WatchaContents;

abstract class WatchaCrawler extends WatchaContents
{
    const SEARCH_COMMON_API = 'http://www.cgv.co.kr/search/?query=';
    const SEARCH_MOVIE_API = 'http://www.cgv.co.kr/search/movie.aspx?query=';
    const CONTENT_API_MOVIE = 'http://www.cgv.co.kr/movies/detail-view/?midx=';
    const CGV_ESTABLISH_DATE = '2000-04-01';

    public $parser;

    protected $search_crawled_cnt = 0;

    public function __construct($content_type)
    {
        parent::__construct($content_type);

        if ($this->isMovieContent()) {
            $this->parser = new WatchaMovieParser($this);
        } else {
        }
    }

    public function buildCommonSearchUrl($search_keyword)
    {
        return self::SEARCH_COMMON_API . $search_keyword;
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

        if (empty($content_tree) === true) {
            Log::error('cgv 검색결과 트리 생성 실패', $content_id);

            return null;
        }

        $title_element = $content_tree->find('html title');
        if ($title_element->exists() && $title_element->text() === 'Object moved') {
            Log::error('검색 결과가 없습니다1.', $api_url);

            return null;
        }

        if ($content_tree->find('.sect-error')->exists()) {
            Log::error('검색 결과가 없습니다2.', $api_url);

            return null;
        }

        return $content_html;
    }
}