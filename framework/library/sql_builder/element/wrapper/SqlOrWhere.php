<?php
namespace framework\library\sql_builder\element\wrapper;

use framework\library\sql_builder\element\clause\SqlWhere;

class SqlOrWhere extends SqlWhere
{
    const CLAUSE = '';

    public function __construct($data)
    {
        parent::__construct($data, false, self::CLAUSE, true);
    }
}