<?php
namespace framework\library\sql_builder\element\wrapper;

use framework\library\sql_builder\element\clause\SqlSelect;

class SqlSubQuery extends SqlSelect
{
    public function __construct($as_name, $from, $data = '*', $where = null, $order_by = null, $limit = null, $group_by = null, $having = null)
    {
        parent::__construct($from, $where, $order_by, $limit, $data, $group_by, $having, true, $as_name);
    }
}