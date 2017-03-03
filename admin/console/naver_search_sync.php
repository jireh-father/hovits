<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('naver_search_sync/movie');
$movie_sync = new \middleware\service\contents\sync\NaverMovieSync();
$movie_sync->syncCrawledContentIds();
