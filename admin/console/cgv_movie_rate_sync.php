<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('cgv_all_movies_rate_sync/movie');
$movie_sync = new \middleware\service\contents\sync\CgvMovieSync();
$result = $movie_sync->syncAllMovieRate();

\framework\library\Log::info('CGV 영화 평점 동기화 결과', $result);
