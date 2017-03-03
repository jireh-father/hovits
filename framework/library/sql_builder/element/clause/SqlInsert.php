<?php
namespace framework\library\sql_builder\element\clause;

use framework\library\sql_builder\element\SqlElement;

class SqlInsert extends SqlElement
{
    const CLAUSE = 'INSERT INTO';
    const CLAUSE_IGNORE_INSERT = 'INSERT IGNORE INTO';
    const GLUE = ', ';

    public function __construct($table, $columns, $values = null, $is_select_values = false, $is_ignore = false)
    {

        if (is_array($columns)) {
            $insert_data = implode(self::GLUE, $columns);
        } else {
            $insert_data = $columns;
        }
        $insert_data = "{$table} ({$insert_data})";
        $element = $is_select_values === true ? $values : new SqlValues($values);
        parent::__construct($insert_data, array($element), $is_ignore === true ? self::CLAUSE_IGNORE_INSERT : self::CLAUSE);
    }
}