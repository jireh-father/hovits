<?php
namespace middleware\service\contents\sync;

use framework\library\File;
use framework\library\Log;
use framework\library\sql_builder\SqlBuilder;
use framework\library\Time;
use middleware\model\Movie;
use middleware\service\contents\CgvContents;
use middleware\service\contents\crawler\CgvCrawler;
use middleware\service\contents\crawler\CgvMovieCrawler;
use middleware\service\contents\crawler\WatchaMovieCrawler;
use middleware\service\contents\parser\CgvParser;
use middleware\service\contents\WatchaContents;

abstract class WatchaSync extends WatchaContents
{
    /**
     * @var CgvParser
     */
    protected $parser = null;

    /**
     * @var CgvCrawler
     */
    protected $crawler = null;

    public function __construct($content_type)
    {
        parent::__construct($content_type);

        if ($this->isMovieContent()) {
            $this->crawler = new WatchaMovieCrawler();
        } else {
            //            $this->crawler = new KoficPeopleCrawler();
        }
        $this->parser = $this->crawler->parser;
    }

    abstract public function syncContentId($search_html, $content, $is_force = true);

    abstract public function syncContentIdDirect($content_id, $is_force = true);
}