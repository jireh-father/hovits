<?php
namespace framework\model;

use framework\base\Model;

class CommonModel extends Model
{
    public static function getInstance($table, $dsn = null)
    {
        $instance = parent::getInstance($dsn);
        $instance->table = $table;

        return $instance;
    }
}