<?php
namespace framework\library\sql_builder\element\clause;

class SqlHaving extends SqlWhere
{
    const CLAUSE = 'HAVING';

    public function __construct($data, $is_and_glue = true)
    {
        parent::__construct($data, null, self::CLAUSE, ($is_and_glue === true ? self::AND_GLUE : self::OR_GLUE));
    }
}