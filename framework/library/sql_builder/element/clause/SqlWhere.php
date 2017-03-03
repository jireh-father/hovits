<?php
namespace framework\library\sql_builder\element\clause;

use framework\library\sql_builder\element\SqlElement;

class SqlWhere extends SqlElement
{
    const CLAUSE = 'WHERE';
    const AND_GLUE = ' AND ';
    const OR_GLUE = ' OR ';

    public function __construct($data, $is_and_glue = true, $clause = null, $is_wrap = false)
    {
        $clause = (isset($clause) ? $clause : self::CLAUSE);
        $glue = ($is_and_glue === true ? self::AND_GLUE : self::OR_GLUE);
        parent::__construct($data, null, $clause, $glue, $is_wrap);
    }

    public function parseValue($key, $value)
    {
        self::$values[] = $value;
        $value = '?';
        if (empty($key)) {
            return $value;
        } else {
            return "{$key} = {$value}";
        }
    }

    public function parseArray(array $array_data, $global_key = null)
    {
        $tmp_array = array();

        foreach ($array_data as $key => $value) {
            if ($value instanceof SqlElement) {
                $tmp_array[] = $value->parse($key);
            } elseif (is_string($value) || is_numeric($value) || is_bool($value)) {
                if (empty($global_key)) {
                    $tmp_array[] = $this->parseValue($key, $value);
                } else {
                    $tmp_array[] = $this->parseValue($global_key, $value);
                }
            } elseif (is_array($value)) {
                if (is_numeric($key)) {
                    $tmp_array[] = $this->parseArray($value);
                } else {
                    $tmp_array[] = $this->parseArray($value, $key);
                }
            }
        }

        return implode($this->getGlue(), $tmp_array);
    }

    public function parse($key = null)
    {
        $sql = $this->parseElement($key);
        if (is_array($this->elements)) {
            foreach ($this->elements as $element) {
                if (is_string($element) || is_numeric($element)) {
                    $sql .= " {$element}";
                } elseif ($element instanceof SqlElement) {
                    $sql .= ' ' . $element->parse();
                }
            }
        }

        if (!empty($sql)) {
            if ($this->is_wrap === true) {
                if (!empty($this->wrapper_as)) {
                    $wrapper_as = ' AS ' . $this->wrapper_as;
                } else {
                    $wrapper_as = '';
                }
                $sql = '(' . $this->clause . $sql . ')' . $wrapper_as;
            } else {
                $sql = $this->clause . ' ' . $sql;
            }
        }

        return $sql;
    }
}