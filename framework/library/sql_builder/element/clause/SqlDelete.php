<?php
namespace framework\library\sql_builder\element\clause;

use framework\library\sql_builder\element\SqlElement;

class SqlDelete extends SqlElement
{
    const CLAUSE = 'DELETE';

    public function __construct($from, $where = null, $using = null, $order_by = null, $limit = null)
    {
        $elements = array(new SqlFrom($from));
        if (!empty($where)) {
            $elements[] = new SqlWhere($where);
        }
        if (!empty($using)) {
            $elements[] = new SqlUsing($using);
        }
        if (!empty($order_by)) {
            $elements[] = new SqlOrderBy($order_by);
        }
        if (!empty($limit)) {
            $elements[] = new SqlLimit($limit);
        }
        parent::__construct('', $elements, self::CLAUSE);
    }
}