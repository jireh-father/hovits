<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('kofic_crawling/movie');
$movie_crawler = new \middleware\service\contents\crawler\KoficMovieCrawler();
$movie_crawler->crawlUpdatedLists();

\framework\library\Log::setLogType('kofic_crawling/people');
$people_crawler = new \middleware\service\contents\crawler\KoficPeopleCrawler();
$people_crawler->crawlUpdatedLists();

\framework\library\Log::setLogType('kofic_sync/movie');
$movie_sync = new \middleware\service\contents\sync\KoficMovieSync();

$movie_sync->syncUpdatedContents(true);

\framework\library\Log::setLogType('kofic_sync/people');
$people_sync = new \middleware\service\contents\sync\KoficPeopleSync();

$people_sync->syncUpdatedContents(true);