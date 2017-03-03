<?php
namespace framework\library\sql_builder\element\clause;

use framework\library\sql_builder\element\SqlElement;

class SqlValues extends SqlElement
{
    const CLAUSE = 'VALUES';
    const GLUE = ', ';

    public function __construct($data)
    {
        parent::__construct($data, null, self::CLAUSE, self::GLUE);
    }

    public function parse($key = null)
    {
        $data = $this->getData();
        $sql = '';
        if (is_string($data) || is_integer($data)) {
            $sql = "({$data})";
        } elseif (is_array($data)) {
            $keys = array_keys($data);
            if (is_numeric($keys[0]) && is_array($data[$keys[0]])) {
                $tmp_values = array();
                foreach ($data as $key => $item) {
                    $tmp_values[] = '(' . $this->parseArray($item, $key) . ')';
                }
                $sql = implode(', ', $tmp_values);
            } else {
                $sql = $this->parseArray($data, $key);
                $sql = "({$sql})";
            }
        } elseif ($data instanceof SqlElement) {
            $sql = $data->parse($key);
            $sql = "({$sql})";
        }

        if (!empty($sql)) {
            $sql = $this->clause . ' ' . $sql;
        }

        return $sql;
    }

    public function parseValue($key, $value)
    {
        self::$values[] = $value;

        return '?';
    }
}