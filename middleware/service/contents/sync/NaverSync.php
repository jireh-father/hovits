<?php
namespace middleware\service\contents\sync;

use middleware\service\contents\crawler\NaverCrawler;
use middleware\service\contents\crawler\NaverMovieCrawler;
use middleware\service\contents\NaverContents;
use middleware\service\contents\parser\NaverParser;

abstract class NaverSync extends NaverContents
{
    /**
     * @var NaverParser
     */
    public $parser = null;

    /**
     * @var NaverCrawler
     */
    public $crawler = null;

    public function __construct($content_type)
    {
        parent::__construct($content_type);

        if ($this->isMovieContent()) {
            $this->crawler = new NaverMovieCrawler();
        } else {
            //            $this->crawler = new KoficPeopleCrawler();
        }
        $this->parser = $this->crawler->parser;
    }

    abstract public function syncContentId($search_json, $content, $is_force = true);

    abstract public function syncContentIdDirect($content_id, $is_force = true);
}