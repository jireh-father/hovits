<?php
namespace middleware\service\contents\parser;

use middleware\service\contents\CgvContents;
use middleware\service\contents\crawler\CgvCrawler;

abstract class CgvParser extends CgvContents
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