<?php
namespace middleware\service\contents\parser;

use framework\library\Log;
use framework\library\String;
use framework\library\Time;
use middleware\library\QpWrapper;
use middleware\service\contents\crawler\LotteCrawler;
use middleware\service\contents\MegaContents;

class MegaParser extends MegaContents
{
    /**
     * @var LotteCrawler
     */
    protected $crawler = null;
    public $content_id;

    public function __construct($crawler = null)
    {
        parent::__construct(CONTENT_TYPE_MOVIE);

        $this->crawler = $crawler;
    }

    /**
     * @param QpWrapper $content_tree
     * @return null
     */
    public function parseMovieList($content_tree)
    {
        if (empty($content_tree) || !$content_tree->exists()) {
            Log::error('컨텐츠 tree 비었음');

            return null;
        }

        $content_tree->onAutoDecodeUtf8();
        $movie_list = array();

        foreach ($content_tree->find('li.item') as $item) {
            $this->_exists(compact('item'));
            if ($item->hasClass('sm_ad')) {
                continue;
            }

            $title_ele = $item->find('.film_title');
            $this->_exists(compact('title_ele'));

            $title = $title_ele->text();
            $this->_empty(compact('title'));

            $onclick = $title_ele->attr('onclick');

            $this->_empty(compact('onclick'));

            $mega_id = String::extractNumbers($onclick);

            $this->_empty(compact('mega_id'));

            $limit_span = $item->find('.sm_film span');
            $this->_exists(compact('limit_span'));

            $limit_grade = $limit_span->text();
            $this->_empty(compact('limit_grade'));

            $d_day = $item->find('.d_day');
            if ($d_day->exists() && !$d_day->hasClass('no_day')) {
                $d_day->find('p')->remove();
                $release_date = trim($d_day->text());
                $release_date = Time::extractDateString($release_date);
                $this->_empty(compact('release_date'));
            }
            $movie_list[] = compact('title', 'mega_id', 'limit_grade', 'release_date');
        }

        $content_tree->offAutoDecodeUtf8();

        return $movie_list;
    }

    public function extractAvgRate($score_box_tree)
    {
        if (empty($score_box_tree) || !$score_box_tree->exists()) {
            Log::error('영화 평점 tree 없음');

            return null;
        }

        $rate_point = $this->extractRatePoint($score_box_tree);
        $rate_cnt = $this->extractRateCount($score_box_tree);

        if ($rate_cnt < 1) {
            return array(null, $rate_cnt);
        }

        return array('point' => $rate_point, 'count' => $rate_cnt);
    }

    public function extractRatePoint($score_box_tree)
    {
        $strong = $score_box_tree->find('strong')->first();
        if (!$strong->exists()) {
            Log::error('점수 엘리먼트 없음', $score_box_tree->html());

            return 0;
        }
        $point_str = $strong->text();
        if (empty($point_str)) {
            return 0;
        }

        return (float)$point_str;
    }

    public function extractRateCount($score_box_tree)
    {
        $span = $score_box_tree->find('span')->first();
        if (!$span->exists()) {
            Log::error('참여 수 엘리먼트 없음', $score_box_tree->html());

            return 0;
        }
        $count_str = $span->text();
        if (empty($count_str)) {
            return 0;
        }

        return (int)$count_str;
    }
}