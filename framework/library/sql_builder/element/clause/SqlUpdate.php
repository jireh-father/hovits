<?php
namespace framework\library\sql_builder\element\clause;

class SqlUpdate extends SqlFrom
{
    const CLAUSE = 'UPDATE';

    public function __construct($update_tables, $set, $where = null, $order_by = null, $limit = null)
    {
        parent::__construct(
            $update_tables,
            self::CLAUSE,
            array(new SqlSet($set), new SqlWhere($where), new SqlOrderBy($order_by), new SqlLimit($limit))
        );
    }
}
