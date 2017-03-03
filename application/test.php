<?php

require_once 'bootstrap_cli.php';

\framework\library\sql_builder\SqlBuilder::beginSelect();
\framework\library\sql_builder\SqlBuilder::select('*');
\framework\library\sql_builder\SqlBuilder::from('common_log');
\framework\library\sql_builder\SqlBuilder::where(array('log_level' => 'ERROR', 'log_id' => 2));
//\framework\library\sql_builder\SqlBuilder::orderBy('dfd');
\framework\library\sql_builder\SqlBuilder::limit('5');
//\framework\library\sql_builder\SqlBuilder::groupBy();
//\framework\library\sql_builder\SqlBuilder::having($having);
list($query, $values) = \framework\library\sql_builder\SqlBuilder::end();
var_dump($query, $values);

$db = \framework\library\Database::getInstance();
$ret = $db->query($query, $values);
$all = $db->fetchAll();
var_dump($ret, $all);