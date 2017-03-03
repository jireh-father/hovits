<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('cgv_search_sync/movie');
$movie_sync = new \middleware\service\contents\sync\CgvMovieSync();
$movie_sync->syncCrawledContentIds();
