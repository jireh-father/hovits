<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('cgv_boxoffice_rate_sync/movie');
$movie_sync = new \middleware\service\contents\sync\CgvMovieSync();
$result = $movie_sync->syncBoxofficeRate();

\framework\library\Log::info('박스오피스 평점 동기화 결과', $result);
