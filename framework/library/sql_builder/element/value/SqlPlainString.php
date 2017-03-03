<?php
namespace framework\library\sql_builder\element\value;

use framework\library\sql_builder\element\SqlElement;

class SqlPlainString extends SqlElement
{
    public function __construct($in_data)
    {
        parent::__construct($in_data);
    }
}