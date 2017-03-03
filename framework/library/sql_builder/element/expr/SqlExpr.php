<?php
namespace framework\library\sql_builder\element\expr;

use framework\library\sql_builder\element\SqlElement;

class SqlExpr extends SqlElement
{
    public function __construct($column, $value, $operator = '=', $is_wrap = false, $wrapper_as = null)
    {
        $expr = array();
        if (is_array($value)) {
            $expr["{$column} {$operator}"] = $this->parseArray($value);
        } elseif (is_numeric($value) || is_string($value) || is_bool($value)) {
            $expr["{$column} {$operator}"] = $value;
        } elseif ($value instanceof SqlElement) {
            $expr["{$column} {$operator}"] = $value->parse($column);
        }

        parent::__construct($expr, null, null, null, $is_wrap, $wrapper_as);
    }

    public function parseArray(array $array_data)
    {
        $tmp_array = array();
        foreach ($array_data as $key => $value) {
            if ($value instanceof SqlElement) {
                $tmp_array[] = $value->parse($key);
            } elseif (is_string($value) || is_numeric($value) || is_bool($value)) {
                $tmp_array[] = $this->parseValue($key, $value);
            } elseif (is_array($value)) {
                $tmp_array[] = $this->parseArray($value);
            }
        }

        return implode($this->getGlue(), $tmp_array);
    }

    public function parseValue($key, $value)
    {
        self::$values[] = $value;

        return "{$key} ?";
    }
}