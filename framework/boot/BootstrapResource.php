<?php
namespace framework\boot;

BootstrapResource::start();

class BootstrapResource extends Bootstrap
{
    public static function start()
    {
        ini_set('mysql.connect_timeout', -1);
        ini_set('default_socket_timeout', -1);
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);

        parent::start();
    }
}