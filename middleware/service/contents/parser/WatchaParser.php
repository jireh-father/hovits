<?php
namespace middleware\service\contents\parser;

use middleware\service\contents\crawler\CgvCrawler;
use middleware\service\contents\WatchaContents;

abstract class WatchaParser extends WatchaContents
{
    /**
     * @var CgvCrawler
     */
    protected $crawler = null;
    public $content_id;

    public function __construct($content_type, $crawler = null)
    {
        parent::__construct($content_type);

        $this->crawler = $crawler;
    }

    abstract function extractContentIdInSearch($search_html, $content);
}