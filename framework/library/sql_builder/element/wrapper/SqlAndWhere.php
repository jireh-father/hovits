<?php
namespace framework\library\sql_builder\element\wrapper;

use framework\library\sql_builder\element\clause\SqlWhere;

class SqlAndWhere extends SqlWhere
{
    const CLAUSE = '';

    public function __construct($data)
    {
        parent::__construct($data, true, self::CLAUSE, true);
    }
}