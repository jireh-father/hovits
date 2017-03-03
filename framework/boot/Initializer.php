<?php

namespace framework\boot;

use framework\core\AutoLoader;

class Initializer
{
    public static function setConfigurations()
    {
        umask(0);

        require_once PATH_APP . "/config/path.php";
        require_once PATH_FW . '/config/path.php';

        require_once PATH_FW_CONFIG . '/constant.php';

        require_once PATH_FW_CONFIG . "/ErrorType.php";

        require_once PATH_APP_CONFIG . '/Config.php';

        if (isset($_REQUEST['d'])) {
            \Config::$ENABLE_VIEW_LOG = true;
        }

        if (\Config::$ENABLE_MIDDLEWARE === true && PATH_MW . '/config/path.php') {
            require_once PATH_MW . '/config/path.php';
        }

        require_once PATH_APP_CONFIG . '/constant.php';
        if (\Config::$ENABLE_MIDDLEWARE === true) {
            require_once PATH_MW_CONFIG . '/constant.php';
        }

        require_once PATH_FW_CONFIG . '/util.php';

        require_once PATH_APP_CONFIG . '/util.php';
        if (\Config::$ENABLE_MIDDLEWARE === true) {
            require_once PATH_MW_CONFIG . '/util.php';
        }

        require_once PATH_FW_CONFIG . '/system_handler.php';
    }

    public static function initFramework()
    {
        if (\Config::$ENABLE_SESSION === true) {
            session_start();
        }

        // handling error, register_shutdown_function, exception
        set_exception_handler('__exceptionHandler');
        register_shutdown_function('__shutdownHandler');

        // check whether that temp directory is writable.
        if (!is_writable(PATH_TEMP)) {
            echo 'The temp directory is not writable.';
        }

        // init auto loader
        require_once dirname(__DIR__) . '/core/AutoLoader.php';
        AutoLoader::initialize();
    }

}