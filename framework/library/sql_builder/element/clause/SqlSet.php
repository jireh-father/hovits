<?php
namespace framework\library\sql_builder\element\clause;

use framework\library\sql_builder\element\SqlElement;

class SqlSet extends SqlElement
{
    const CLAUSE = 'SET';
    const GLUE = ',';

    public function __construct($data)
    {
        parent::__construct($data, null, self::CLAUSE, self::GLUE);
    }

    public function parseValue($key, $value)
    {
        self::$values[] = $value;
        $value = '?';
        if (empty($key)) {
            return $value;
        }

        return "{$key} = {$value}";
    }
}