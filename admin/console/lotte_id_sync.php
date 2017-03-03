<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('lotte_id_sync/movie');
$movie_sync = new \middleware\service\contents\sync\LotteSync();
$movie_sync->syncAllContentId();
