<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('poster_small_thumb');
\middleware\service\contents\thumb\ThumbMaker::makeSmallImagePoster('main');