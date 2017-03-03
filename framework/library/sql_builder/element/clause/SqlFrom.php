<?php
namespace framework\library\sql_builder\element\clause;

use framework\library\sql_builder\element\expr\SqlJoin;
use framework\library\sql_builder\element\SqlElement;

class SqlFrom extends SqlElement
{
    const CLAUSE = 'FROM';
    const GLUE = '';
    const FROM_GLUE = ', ';

    public function __construct($data, $clause = null, $elements = null)
    {
        $clause = (empty($clause) ? self::CLAUSE : $clause);
        parent::__construct($data, $elements, $clause, self::GLUE);
    }

    public function parseArray(array $array_data, $global_key = null)
    {
        $tmp_array = array();
        foreach ($array_data as $key => $value) {
            if ($value instanceof SqlElement) {
                if ($value instanceof SqlJoin) {
                    $tmp_array[] = $value->parse();
                } else {
                    $tmp_array[] = self::FROM_GLUE . $value->parse();
                }
            } elseif (is_string($value)) {
                $tmp_array[] = self::FROM_GLUE . $this->parseValue($key, $value);
            }
        }

        $from = implode($this->getGlue(), $tmp_array);
        if (strpos($from, self::FROM_GLUE) === 0) {
            $from = substr($from, 2);
        }

        return $from;
    }

    public function parseValue($key, $value)
    {
        if (is_numeric($key) || empty($key)) {
            return $value;
        } else {
            return "{$value} AS {$key}";
        }
    }
}