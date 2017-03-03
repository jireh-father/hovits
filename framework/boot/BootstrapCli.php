<?php
namespace framework\boot;

if (defined('IS_CONSOLE') && IS_CONSOLE === true) {
    if (!empty($argv[1])) {
        $php_file = $argv[1];
    }

    if (count($argv) > 2) {
        array_shift($argv);
        array_shift($argv);
    } else {
        $argv = null;
    }
    BootstrapCli::start($php_file, $argv);
} else {
    BootstrapCli::start();
}


class BootstrapCli extends Bootstrap
{
    public static function start($php_file = null, $argv = null)
    {
        ini_set('mysql.connect_timeout', -1);
        ini_set('default_socket_timeout', -1);
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);

        parent::start();

        /**
         * INIT SYSTEM
         */
        Initializer::initFramework();

        //todo: run script
        if (defined('IS_CONSOLE') && IS_CONSOLE === true) {
            $php_path = PATH_APP_CONSOLE . "/{$php_file}.php";
            if (is_file($php_path) === false) {
                echo "The console file is invalid.({$php_path})";
                exit;
            }

            require_once PATH_FW_BASE . '/Console.php';
            require_once $php_path;

            $class_name = basename($php_file);

            if (class_exists($class_name) === false) {
                echo "There is no the class.({$class_name})";
                exit;
            }

            $runner = new $class_name();
            $runner->run($argv);

            parent::finish($php_path, $class_name, $argv);
        }
    }
}