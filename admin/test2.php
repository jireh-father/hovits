<?php
require_once 'bootstrap_cli.php';

$model = \framework\model\CommonLog::getInstance();
$list = $model->getList(
    array(
//        \framework\library\sql_builder\SqlBuilder::dateRange(
//            'insert_time',
//            '2015-09-10 13:22:00'//,
        //            '2015-08-26 17:59:24'
//        ),
//        'log_level' => LOG_LEVEL_ERROR,
//        'log_type'  => 'naver_search_sync/movie',
        'log_msg'   => '감독이나 배우만 같음'
        //        \framework\library\sql_builder\SqlBuilder::wildcard('log_msg', '크롤링 실패', \framework\library\sql_builder\element\value\SqlWildcard::WILDCARD_LOCATION_LAST)
    )
);
var_dump(count($list));
$result = array();
foreach ($list as $item) {
    $log_data = json_decode($item['log_data'], true);
    $result[] = $log_data[1];
    //    echo $log_data . ',';
}

$result = array_unique($result);
for($i=count($result) -1 ;$i>=0;$i--) {
    echo $result[$i] . ',';
}
exit;
echo implode(',', $result);

