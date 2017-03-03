<?php

namespace framework\library\sql_builder\element;

abstract class SqlElement
{
    /**
     * @var SqlElement[] || string[]
     */
    protected $elements;
    protected $data;
    protected $clause;
    protected $glue;
    protected $is_wrap;
    protected $wrapper_as;

    public static $values = array();

    public function __construct(
        $data,
        $elements = null,
        $clause = null,
        $glue = null,
        $is_wrap = false,
        $wrapper_as = ''
    )
    {
        $this->data = $data;
        $this->setElements($elements);
        $this->clause = $clause;
        $this->glue = $glue;
        $this->is_wrap = $is_wrap;
        $this->wrapper_as = $wrapper_as;
    }

    final public function initValues()
    {
        self::$values = array();
    }

    final protected function getData()
    {
        return $this->data;
    }

    final protected function getGlue()
    {
        return $this->glue;
    }

    final public function setElements($elements)
    {
        if (!empty($elements)) {
            $this->elements = is_array($elements) ? $elements : array($elements);
        }

        return $this;
    }

    final public function addElement(SqlElement $sql_element)
    {
        $this->elements[] = $sql_element;

        return $this;
    }

    final public function removeElement(SqlElement $target_sql_element)
    {
        foreach ($this->elements as $i => $sql_element) {
            if ($target_sql_element === $sql_element) {
                unset($this->elements[$i]);

                break;
            }
        }

        return $this;
    }

    final public function getValues()
    {
        return self::$values;
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

    public function parseElement($key = null)
    {
        $data = $this->getData();
        $sql = '';

        if (is_array($data)) {
            $sql = $this->parseArray($data, $key);
        } elseif (is_string($data) || is_numeric($data)) {
            $sql = $this->parseValue($key, $data);
        } elseif ($data instanceof SqlElement) {
            $sql = $data->parse($key);
        }

        return $sql;
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

    public function parseValue($key, $value)
    {
        return $value;
    }
}