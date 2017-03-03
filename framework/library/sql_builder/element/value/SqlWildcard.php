<?php
namespace framework\library\sql_builder\element\value;

use framework\library\sql_builder\element\SqlElement;

class SqlWildcard extends SqlElement
{
    const WILDCARD_LOCATION_BOTH = 1;
    const WILDCARD_LOCATION_FIRST = 2;
    const WILDCARD_LOCATION_LAST = 3;

    public function __construct($key, $value, $location = self::WILDCARD_LOCATION_BOTH, $wildcard_type = '%')
    {

        switch ($location) {
            case self::WILDCARD_LOCATION_BOTH:
                $value = "{$wildcard_type}{$value}{$wildcard_type}";
                break;
            case self::WILDCARD_LOCATION_FIRST:
                $value = "{$wildcard_type}{$value}";
                break;
            case self::WILDCARD_LOCATION_LAST:
                $value = "{$value}{$wildcard_type}";
                break;
            default:
                break;
        }

        parent::__construct(array("{$key} LIKE " => $value));
    }

    public function parseArray(array $array_data)
    {
        $tmp_array = array();
        foreach ($array_data as $key => $value) {
            $tmp_array[] = $this->parseValue($key, $value);
        }

        return implode($this->getGlue(), $tmp_array);
    }

    public function parseValue($key, $value)
    {
        self::$values[] = $value;

        return "{$key} ?";
    }
}