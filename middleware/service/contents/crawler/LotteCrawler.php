<?php
namespace middleware\service\contents\crawler;

use framework\library\Log;
use middleware\exception\CrawlerException;
use middleware\library\Curl;
use middleware\library\QpWrapper;
use middleware\service\contents\LotteContents;
use middleware\service\contents\parser\LotteParser;

class LotteCrawler extends LotteContents
{
    const MOVIE_LIST_API = 'http://www.lottecinema.co.kr/LHS/LHFS/Ticket/selection/GetMCDData.aspx';
    const CONTENT_API_MOVIE = 'http://www.lottecinema.co.kr/LHS/LHFS/Contents/MovieInfo/MovieInfoContent.aspx?MovieInfoCode=';

    public $parser;

    protected $search_crawled_cnt = 0;

    public function __construct()
    {
        parent::__construct(CONTENT_TYPE_MOVIE);

        if ($this->isMovieContent()) {
            $this->parser = new LotteParser($this);
        } else {
        }
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

    public function getAllDataList()
    {
        try {
            $list = Curl::post(self::MOVIE_LIST_API, null, 3, array('Content-Length:0'));
        } catch (\Exception $e) {
            Log::error('curl 호출실패', array(self::MOVIE_LIST_API, $e));

            return null;
        }

        return $list;
    }

    public function getBoxOfficeList()
    {
        $list_json = $this->getAllDataList();

        if (empty($list_json)) {
            Log::error('롯데시네마 데이터 비었음');

            return null;
        }

        $list = json_decode($list_json, true);

        if (empty($list)) {
            Log::error('롯데시네마 데이터 json 형식 깨짐', $list_json);

            return null;
        }

        if (empty($list['movies'])) {
            Log::error('롯데시네마 데이터 영화정보 비었음');

            return null;
        }

        return $this->filterUniqueMovies($list['movies']);
    }

    public function filterUniqueMovies($movies)
    {
        if (empty($movies)) {
            return null;
        }

        $filter_movies = array();
        foreach ($movies as $movie) {
            $lotte_id = $movie['contentCode'];
            if (empty($lotte_id)) {
                throw new CrawlerException('롯데 아이디가 데이터에 없음', $movie);
            }
            if (empty($filter_movies[$lotte_id])) {
                $filter_movies[$lotte_id] = $movie;
            }
        }

        return $filter_movies;
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

        $content_tree = QpWrapper::getInstance($content_html, '#contentwarp .movie_detail_infor');

        if (empty($content_tree) === true) {
            Log::error('lotte 검색결과 트리 생성 실패', $content_id);

            return null;
        }

        $title = $content_tree->find('.movie_detail_txt strong.title')->text();

        if (empty($title)) {
            Log::error('영화 상세 페이지가 이상합니다.', array($api_url, $content_id));

            return null;
        }

        return $content_html;
    }
}