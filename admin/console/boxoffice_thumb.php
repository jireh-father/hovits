<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('kofic_thumbs/boxoffice');
\middleware\service\contents\thumb\ThumbMaker::makeBoxOfficeThumbs();