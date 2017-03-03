<?php
namespace framework\library\sql_builder\element\clause;

use framework\library\sql_builder\element\SqlElement;

class SqlSelect extends SqlElement
{
    const CLAUSE = 'SELECT';
    const GLUE = ', ';

    public function __construct(
        $from,
        $where = null,
        $order_by = null,
        $limit = null,
        $data = '*',
        $group_by = null,
        $having = null,
        $is_sub_query = false,
        $as_name = null
    ) {
        $elements = array();
        if (!empty($from)) {
            $elements[] = new SqlFrom($from);
        }
        if (!empty($where)) {
            $elements[] = new SqlWhere($where);
        }
        if (!empty($group_by)) {
            $elements[] = new SqlGroupBy($group_by);
        }
        if (!empty($having)) {
            $elements[] = new SqlHaving($having);
        }
        if (!empty($order_by)) {
            $elements[] = new SqlOrderBy($order_by);
        }
        if (!empty($limit)) {
            $elements[] = new SqlLimit($limit);
        }
        parent::__construct($data, $elements, self::CLAUSE, self::GLUE, $is_sub_query, $as_name);
    }

    public function parseValue($key, $value)
    {
        if (is_numeric($key) || empty($key)) {
            return " {$value}";
        } else {
            return " {$value} AS {$key}";
        }
    }
}