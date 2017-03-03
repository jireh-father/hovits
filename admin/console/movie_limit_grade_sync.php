<?php
require dirname(__DIR__) . '/bootstrap_cli.php';

$model = \middleware\model\Movie::getInstance();
$limit_grades = $model->getValues('limit_grade', null, null, null, null, 'limit_grade', array(\framework\library\sql_builder\SqlBuilder::isNotNull('limit_grade')));
$limit_model = \middleware\model\MovieLimitGrade::getInstance();
foreach ($limit_grades as $limit_grade) {
    $limit_model->set(compact('limit_grade'), compact('limit_grade'));
}

