<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('mega_all_movies_rate_sync/movie');
$movie_sync = new \middleware\service\contents\sync\MegaSync();
$result = $movie_sync->syncAllMovieRate();

\framework\library\Log::info('메가박스 영화 평점 동기화 결과', $result);
