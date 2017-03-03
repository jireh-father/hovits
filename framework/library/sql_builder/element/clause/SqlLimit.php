<?php
namespace framework\library\sql_builder\element\clause;

use framework\library\sql_builder\element\SqlElement;

class SqlLimit extends SqlElement
{
    const CLAUSE_NAME = 'LIMIT';

    public function __construct($limit, $offset = null)
    {

        if (empty($offset)) {
            $data = "{$limit}";
        } else {
            $data = "{$limit} OFFSET {$offset}";
        }
        parent::__construct($data, null, self::CLAUSE_NAME);
    }
}