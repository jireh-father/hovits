<?php

namespace controller\server;

use controller\AdminBase;

class PhpFunction extends AdminBase
{
    private static $command_list = array(
        "hgup" => 'hg pull & hg up'
    );

    private static $function_types = array(
        ''   => 'function',
        '::' => 'static',
        '->' => 'method'
    );

    public function index()
    {
        $this->addJs('server/cli');
        $this->setView('server/php_function', array('command_list' => self::$command_list, 'function_types' => self::$function_types));
    }
}