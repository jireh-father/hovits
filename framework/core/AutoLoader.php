<?php
namespace framework\core;

class AutoLoader
{
    private static $is_initialized = false;

    public static function initialize()
    {
        if (self::$is_initialized) {
            echo "Initializing auto loader is already done.";
        }

        $fw_auto_load_list = require_once PATH_FW_CONFIG . '/autoload.php';
        $app_auto_load_list = require_once PATH_APP_CONFIG . '/autoload.php';

        $auto_load_files = array_merge($fw_auto_load_list['file'], $app_auto_load_list['file']);
        $auto_load_dirs = array_merge($fw_auto_load_list['dir'], $app_auto_load_list['dir']);

        if (\Config::$ENABLE_MIDDLEWARE === true) {
            $mw_auto_load_list = require_once PATH_MW_CONFIG . '/autoload.php';
            $auto_load_files = array_merge($auto_load_files, $mw_auto_load_list['file']);
            $auto_load_dirs = array_merge($auto_load_dirs, $mw_auto_load_list['dir']);
        }

        //auto load files
        foreach ($auto_load_files as $file_path) {
            if (is_file($file_path)) {
                require_once $file_path;
            } else {
                echo "Auto load files Not Found Error.";
            }
        }

        try {
            $loader = require PATH_VENDOR . "/autoload.php";
        } catch (\Exception $e) {
            echo 'Composer Auto Loader Exception : ' . $e->getMessage();
        }

        foreach ($auto_load_dirs as $dir) {
            $loader->add($dir[0], $dir[1]);
        }

        self::$is_initialized = true;
    }

}