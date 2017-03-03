<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('cgv_search_crawling/movie');
$movie_crawler = new \middleware\service\contents\crawler\CgvMovieCrawler();
$movie_crawler->crawlEmptySearchPages();
