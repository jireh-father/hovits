<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('naver_search_crawling/movie');
$movie_crawler = new \middleware\service\contents\crawler\NaverMovieCrawler();
$movie_crawler->crawlEmptySearchPages();