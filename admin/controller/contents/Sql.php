<?php
namespace controller\contents;

use controller\AdminBase;
use framework\library\Database;
use framework\library\sql_builder\SqlBuilder;

class Sql extends AdminBase
{
    public function index()
    {

    }

    public function query()
    {
        $query = $this->getParam('query');
        $database = Database::getInstance();
        $ret = $database->query($query);
        if ($ret === true && $database->getSqlType() === SqlBuilder::SQL_TYPE_SELECT) {
            $ret = $database->fetchAll(\PDO::FETCH_ASSOC);
        }

        debug($ret);
        exit;
    }
}