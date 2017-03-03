<?php
namespace middleware\service\contents\crawler;

class KoficPeopleCrawler extends KoficCrawler
{
    public function __construct()
    {
        parent::__construct(CONTENT_TYPE_PEOPLE);
    }
}