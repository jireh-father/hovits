<?php
namespace framework\library\sql_builder\element\clause;

use framework\library\sql_builder\element\SqlElement;

class SqlOrderBy extends SqlElement
{
    const CLAUSE = 'ORDER BY';
    const GLUE = ', ';

    public function __construct($data)
    {
        parent::__construct($data, null, self::CLAUSE, self::GLUE);
    }

    public function parseValue($key, $value)
    {
        if (is_numeric($key)) {
            return $value;
        } else {
            return "$key $value";
        }
    }
}