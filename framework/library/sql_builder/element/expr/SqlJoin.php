<?php
namespace framework\library\sql_builder\element\expr;

use framework\library\sql_builder\element\SqlElement;

class SqlJoin extends SqlElement
{
    public function __construct($join_table, $on_expr = null, $join_type = ' JOIN')
    {
        $join_stmt = '';
        if (is_array($join_table)) {
            $keys = array_keys($join_table);
            $values = array_values($join_table);
            $join_stmt = "{$values[0]} AS {$keys[0]}";
        } elseif (is_string($join_table)) {
            $join_stmt = $join_table;
        } elseif ($join_table instanceof SqlElement) {
            $join_stmt = $join_table->parse();
        }
        if (!empty($on_expr)) {
            if (is_string($on_expr)) {
                $join_stmt .= " ON {$on_expr}";
            } elseif ($on_expr instanceof SqlElement) {
                $join_stmt .= ' ON' . $on_expr->parse();
            }
        }

        parent::__construct($join_stmt, null, $join_type);
    }
}