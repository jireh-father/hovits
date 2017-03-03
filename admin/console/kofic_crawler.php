<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('kofic_crawling/movie');
$movie_crawler = new \middleware\service\contents\crawler\KoficMovieCrawler();
$movie_crawler->crawlUpdatedLists();

\framework\library\Log::setLogType('kofic_crawling/people');
$people_crawler = new \middleware\service\contents\crawler\KoficPeopleCrawler();
$people_crawler->crawlUpdatedLists();