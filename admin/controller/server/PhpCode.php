<?php

namespace controller\server;

use controller\AdminBase;

class PhpCode extends AdminBase
{
    private static $command_list = array();

    public function index()
    {
        $this->addJs('server/cli');
        $this->setView('server/php_code', array('command_list' => self::$command_list));
    }
}