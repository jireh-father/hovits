<?php
namespace middleware\service\contents\parser;

use framework\library\Log;
use framework\library\String;
use middleware\service\contents\crawler\LotteCrawler;
use middleware\service\contents\LotteContents;

class LotteParser extends LotteContents
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
        $em = $score_box_tree->find('.score1 .score_on');
        if (!$em->exists()) {
            return 0;
        }
        $point_str = $em->attr('style');
        if (empty($point_str)) {
            return 0;
        }

        return (int)String::extractNumbers($point_str);
    }

    public function extractRateCount($score_box_tree)
    {
        $em = $score_box_tree->find('.score1 .score_on .txt_lay');
        if (!$em->exists()) {
            return 0;
        }
        $count_str = $em->text();
        if (empty($count_str)) {
            return 0;
        }

        return (int)String::extractNumbers($count_str);
    }
}