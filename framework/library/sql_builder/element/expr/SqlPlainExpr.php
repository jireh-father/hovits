<?php
namespace framework\library\sql_builder\element\expr;

use framework\library\sql_builder\element\SqlElement;

class SqlPlainExpr extends SqlElement
{
    public function __construct($column, $value, $operator = '=', $is_wrap = false, $wrapper_as = null)
    {
        $expr = '';
        if (is_array($value)) {
            $expr = "{$column} {$operator} " . $this->parseArray($value);
        } elseif (is_string($value) || is_int($value)) {
            $expr = "{$column} {$operator} {$value}";
        } elseif ($value instanceof SqlElement) {
            $expr = "{$column} {$operator} " . $value->parse($column);
        }

        parent::__construct($expr, null, null, null, $is_wrap, $wrapper_as);
    }
}