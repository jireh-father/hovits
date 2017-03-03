<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

\framework\library\Log::setLogType('kofic_crawler/boxoffice_excel');
$crawler = new \middleware\service\contents\crawler\KoficMovieCrawler();
$crawler->crawlAllBoxOfficeExcel();