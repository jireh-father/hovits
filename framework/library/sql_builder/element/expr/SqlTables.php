<?php
namespace framework\library\sql_builder\element\expr;

use framework\library\sql_builder\element\clause\SqlFrom;

class SqlTables extends SqlFrom
{
    const CLAUSE = '';

    public function __construct($data)
    {
        parent::__construct($data, self::CLAUSE);
    }
}
