<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('mega_id_sync/movie');
$movie_sync = new \middleware\service\contents\sync\MegaSync();
$movie_sync->syncAllContentId();
