<?php
namespace framework\library\sql_builder\element\clause;

use framework\library\sql_builder\element\SqlElement;

class SqlGroupBy extends SqlElement
{
    const CLAUSE = 'GROUP BY';
    const GLUE = ', ';

    public function __construct($data)
    {
        parent::__construct($data, null, self::CLAUSE, self::GLUE);
    }
}