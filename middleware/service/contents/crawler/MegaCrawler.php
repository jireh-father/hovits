<?php
namespace middleware\service\contents\crawler;

use framework\library\Log;
use middleware\library\Curl;
use middleware\library\QpWrapper;
use middleware\service\contents\MegaContents;
use middleware\service\contents\parser\MegaParser;

class MegaCrawler extends MegaContents
{
    const MOVIE_LIST_API = 'http://www.megabox.co.kr/pages/movie/Movie_List.jsp';
    const CONTENT_API_MOVIE = 'http://www.megabox.co.kr/pages/movie/Movie_Detail.jsp';

    public $parser;

    protected $search_crawled_cnt = 0;

    public function __construct()
    {
        parent::__construct(CONTENT_TYPE_MOVIE);

        if ($this->isMovieContent()) {
            $this->parser = new MegaParser($this);
        } else {
        }
    }

    public function getBoxOfficeList(&$html_tree = null)
    {
        try {
            $data = array(
                'menuId'  => 'movie',
                'startNo' => '0',
                'count'   => '1000',
                'sort'    => 'releaseDate'
            );
            $list_html = Curl::post(self::MOVIE_LIST_API, $data, 3);
        } catch (\Exception $e) {
            Log::error('curl 호출실패', array(self::MOVIE_LIST_API, $e));

            return null;
        }

        if (empty($list_html)) {
            Log::error('메가박스 데이터 비었음');

            return null;
        }

        $html_tree = QpWrapper::getInstance($list_html);
        if (!$html_tree->exists()) {
            Log::error('메가박스 데이터 html 파싱 실패');

            return null;
        }

        $li = $html_tree->find('li.item');
        if (!$li->exists()) {
            Log::error('메가박스 데이터 html 영화리스트 없음');

            return null;
        }

        return $list_html;
    }

    public function getContent($content_id, &$content_tree = null)
    {
        if (empty($content_id)) {
            return null;
        }

        try {
            $content_html = Curl::post(self::CONTENT_API_MOVIE, array('code' => $content_id), 3, null, 15, 7);
        } catch (\Exception $e) {
            Log::error('curl 실패', array(self::CONTENT_API_MOVIE, $content_id));

            return null;
        }

        if (empty($content_html)) {
            Log::error('검색 실패(내용 비었음)', $content_id);

            return null;
        }

        $content_tree = QpWrapper::getInstance($content_html, '.right_wrap');

        if (empty($content_tree) === true) {
            Log::error('메가박스 검색결과 트리 생성 실패', $content_id);

            return null;
        }

        return $content_html;
    }
}