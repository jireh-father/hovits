<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('naver_all_movies_rate_sync/movie');
$movie_sync = new \middleware\service\contents\sync\NaverMovieSync();
$result = $movie_sync->syncAllMovieRate();

\framework\library\Log::info('네이버 영화 평점 동기화 결과', $result);
