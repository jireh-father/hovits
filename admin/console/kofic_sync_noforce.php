<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('kofic_sync/movie');
$movie_sync = new \middleware\service\contents\sync\KoficMovieSync();

$movie_sync->syncUpdatedContents();

\framework\library\Log::setLogType('kofic_sync/people');
$people_sync = new \middleware\service\contents\sync\KoficPeopleSync();

$people_sync->syncUpdatedContents();