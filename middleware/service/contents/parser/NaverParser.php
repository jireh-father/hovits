<?php
namespace middleware\service\contents\parser;

use middleware\service\contents\crawler\NaverCrawler;
use middleware\service\contents\NaverContents;

abstract class NaverParser extends NaverContents
{
    /**
     * @var NaverCrawler
     */
    protected $crawler = null;
    public $content_id;

    public function __construct($content_type, $crawler = null)
    {
        parent::__construct($content_type);

        $this->crawler = $crawler;
    }

    abstract function extractContentIdInSearch($search_array, $content);
}