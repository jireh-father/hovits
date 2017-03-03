<?php
namespace framework\library\sql_builder\element\expr;

use framework\library\sql_builder\element\SqlElement;
use framework\library\sql_builder\element\wrapper\SqlAndWhere;

class SqlDateRange extends SqlElement
{
    public function __construct($column, $from = null, $to = null)
    {
        $data = null;
        if (!empty($from) && !empty($to)) {
            $data = new SqlAndWhere(
                array(
                    new SqlExpr($column, $from, '>='),
                    new SqlExpr($column, $to, '<=')
                )
            );
        } elseif (!empty($from)) {
            $data = new SqlExpr($column, $from, '>=');
        } elseif (!empty($to)) {
            $data = new SqlExpr($column, $to, '<=');
        }

        parent::__construct($data);
    }
}