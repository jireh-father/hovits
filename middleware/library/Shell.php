<?php
namespace middleware\library;

class Shell
{
    const ASYNC_COMMAND = ' > /dev/null &';


    public static function execAsync($sCommand)
    {
        return exec('nohup ' . $sCommand . self::ASYNC_COMMAND);
    }

    public static function exec($sCommand, array &$output = null, &$return_var = null)
    {
        return exec($sCommand, $output, $return_var);
    }
}