<?php
namespace framework\library\sql_builder\element\wrapper;

use framework\library\sql_builder\element\SqlElement;

class SqlWrapper extends SqlElement
{
    const CLAUSE = '';
    /**
     * @var SqlElement
     */
    private $inner_obj;

    public function __construct($data, $glue = ', ', $inner_obj = null)
    {
        $this->inner_obj = $inner_obj;
        parent::__construct($data, null, self::CLAUSE, $glue, true);
    }

    public function parseArray(array $array_data, $global_key = null)
    {
        $tmp_array = array();
        foreach ($array_data as $key => $value) {
            if ($value instanceof SqlElement) {
                $tmp_array[] = $value->parse();
            } elseif (is_string($value)) {
                if (empty($this->inner_obj)) {
                    $tmp_array[] = $this->parseValue($key, $value);
                } else {
                    $tmp_array[] = $this->inner_obj->parseValue($key, $value);
                }
            }
        }

        return implode($this->getGlue(), $tmp_array);
    }
}