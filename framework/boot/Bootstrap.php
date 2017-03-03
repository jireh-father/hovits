<?php
namespace framework\boot;

use framework\core\Request;
use framework\library\Log;
use framework\library\Time;

abstract class Bootstrap
{
    public static function start()
    {
        /**
         * Set Request Time
         */
        if (!isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
        }

        if (!is_dir(PATH_APP)) {
            echo 'PATH_APP is not a directory.';
            exit;
        }
        /**
         * 한번만 실행
         */
        if (defined('BOOTSTRAP_IS_DONE') && BOOTSTRAP_IS_DONE === true) {
            echo 'Bootstrap is already done!';
            exit;
        }

        require_once __DIR__ . '/Initializer.php';

        /**
         * INIT CONFIGURATION
         */
        Initializer::setConfigurations();

        define('BOOTSTRAP_IS_DONE', true);
    }

    /**
     * @param Request $request
     * @param null $class_name
     * @param null $argv
     */
    protected static function finish($request, $class_name = null, $argv = null)
    {
        if (\Config::$ENABLE_REQUEST_LOG === false) {
            return false;
        }

        /**
         * log request time spent
         */
        $time = Time::getExecutionTime();
        if (empty($class_name)) {
            Log::setLogType('http request');
            $msg = 'http request finish';
            $log_data = array(
                'execution_time' => $time,
                'controller'     => $request->getControllerName(),
                'action'         => $request->getActionName(),
                'method'         => $request->getMethod(),
                'params'         => $request->getParams()

            );
        } else {
            Log::setLogType('cli execution');
            $msg = 'cli execution finish';
            $log_data = array(
                'execution_time' => $time,
                'class'          => $class_name,
                'file'           => $request,
                'params'         => $argv
            );
        }


        Log::info($msg, $log_data);
        Log::restoreLogType();
    }
}