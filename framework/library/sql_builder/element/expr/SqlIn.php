<?php
namespace framework\library\sql_builder\element\expr;

use framework\library\sql_builder\element\SqlElement;
use framework\library\sql_builder\element\wrapper\SqlWrapper;

class SqlIn extends SqlElement
{
    const CLAUSE = 'IN';
    const GLUE = ', ';

    private $column;

    public function __construct($in_data, $column = null)
    {
        $this->column = $column;
        parent::__construct($in_data, null, self::CLAUSE, self::GLUE);
    }

    public function parse($key = null)
    {
        $sql = $this->parseElement($key);
        if (empty($key) || is_numeric($key)) {
            $key = $this->column;
        }
        if (!empty($sql)) {
            $sql = $key . ' ' . $this->clause . ' (' . $sql . ')';
        }

        return $sql;
    }

    public function parseValue($key, $value)
    {
        if (is_string($value) || is_integer($value)) {
            self::$values[] = $value;
            $value = '?';
        } elseif ($value instanceof SqlElement) {
            $value = $value->parse($key);
        }

        return $value;
    }
}