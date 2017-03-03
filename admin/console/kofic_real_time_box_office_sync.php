<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

//sleep(rand(3, 30));

\framework\library\Log::setLogType('kofic_real_time_box_office_sync');
$movie_sync = new \middleware\service\contents\sync\KoficMovieSync();
$movie_sync->syncRealTimeBoxOffice();