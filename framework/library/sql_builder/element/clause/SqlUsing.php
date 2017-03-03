<?php
namespace framework\library\sql_builder\element\clause;

class SqlUsing extends SqlFrom
{
    const CLAUSE = 'USING';

    public function __construct($data)
    {
        parent::__construct($data, self::CLAUSE);
    }
}