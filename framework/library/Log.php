<?php
namespace framework\library;

use framework\model\CommonLog;

class Log
{
    const CRITICAL = LOG_LEVEL_CRITICAL;
    const ERROR = LOG_LEVEL_ERROR;
    const WARNING = LOG_LEVEL_WARNING;
    const INFO = LOG_LEVEL_INFO;
    const DEBUG = LOG_LEVEL_DEBUG;
    const ALL = LOG_LEVEL_ALL;

    public static $LOG_LEVEL = array(
        self::ALL      => 0,
        self::DEBUG    => 1,
        self::INFO     => 2,
        self::WARNING  => 3,
        self::ERROR    => 4,
        self::CRITICAL => 5
    );

    private static $disabled = false;
    private static $file_disabled = false;
    private static $db_disabled = false;
    private static $view_disabled = false;

    private static $old_disabled = false;
    private static $old_file_disabled = false;
    private static $old_db_disabled = false;
    private static $old_view_disabled = false;

    private static $trace_id = null;
    private static $log_type = null;
    private static $old_log_type = null;

    public static function genTraceId()
    {
        list($iUsec, $iSec) = explode(" ", microtime());
        $sTime = substr(date("YmdHis", $iSec), 2) . substr(substr($iUsec, 2), 0, -4);

        return uniqid($sTime) . chr(rand(48, 57)) . chr(rand(65, 90)) . chr(rand(97, 122));
    }

    public static function getTraceId()
    {
        return self::$trace_id;
    }

    public static function setTraceId($iTraceId)
    {
        self::$trace_id = $iTraceId;
    }

    public static function removeTraceId()
    {
        self::$trace_id = null;
    }

    public static function setLogType($log_type)
    {
        self::$old_log_type = self::$log_type;
        self::$log_type = $log_type;
    }

    public static function getLogType()
    {
        return self::$log_type;
    }

    public static function restoreLogType()
    {
        self::$log_type = self::$old_log_type;
    }

    public static function enable()
    {
        self::$old_disabled = self::$disabled;
        self::$disabled = false;
    }

    public static function enableDb()
    {
        self::$old_db_disabled = self::$db_disabled;
        self::$db_disabled = false;
    }

    public static function enableView()
    {
        self::$old_view_disabled = self::$view_disabled;
        self::$view_disabled = false;
    }

    public static function enableFile()
    {
        self::$old_file_disabled = self::$file_disabled;
        self::$file_disabled = false;
    }

    public static function disable()
    {
        self::$old_disabled = self::$disabled;
        self::$disabled = true;
    }

    public static function disableDb()
    {
        self::$old_db_disabled = self::$db_disabled;
        self::$db_disabled = true;
    }

    public static function disableView()
    {
        self::$old_view_disabled = self::$view_disabled;
        self::$view_disabled = true;
    }

    public static function disableFile()
    {
        self::$old_file_disabled = self::$file_disabled;
        self::$file_disabled = true;
    }

    public static function restoreDisable()
    {
        self::$disabled = self::$old_disabled;
    }

    public static function restoreDisableFile()
    {
        self::$file_disabled = self::$old_file_disabled;
    }

    public static function restoreDisableDb()
    {
        self::$db_disabled = self::$old_db_disabled;
    }

    public static function restoreDisableView()
    {
        self::$view_disabled = self::$old_view_disabled;
    }

    public static function debug($log_msg, $log_data = null, $caller_stack_idx = 2)
    {
        self::log($log_msg, $log_data, self::DEBUG, $caller_stack_idx);
    }

    public static function info($log_msg, $log_data = null, $caller_stack_idx = 2)
    {
        self::log($log_msg, $log_data, self::INFO, $caller_stack_idx);
    }

    public static function warning($log_msg, $log_data = null, $caller_stack_idx = 2)
    {
        self::log($log_msg, $log_data, self::WARNING, $caller_stack_idx);
    }

    public static function error($log_msg, $log_data = null, $caller_stack_idx = 2)
    {
        self::log($log_msg, $log_data, self::ERROR, $caller_stack_idx);
    }

    public static function critical($log_msg, $log_data = null, $caller_stack_idx = 2)
    {
        self::log($log_msg, $log_data, self::CRITICAL, $caller_stack_idx);
    }

    private static function getLogCaller($back_trace, $caller_stack_idx)
    {
        if (empty($back_trace[$caller_stack_idx]) === true) {
            $back_trace = $back_trace[$caller_stack_idx - 1];
            $sFile = $back_trace['file'];
            $aPathInfo = pathinfo($sFile);

            $log_caller = basename($aPathInfo['dirname']) . '/' . $aPathInfo['filename'];
        } else {
            $back_trace = $back_trace[$caller_stack_idx];
            $sClass = empty($back_trace['class']) === true ? '' : $back_trace['class'] . '/';
            $sClass = baseClassName($sClass);
            $sFunction = $back_trace['function'];
            $log_caller = $sClass . '/' . $sFunction;
        }

        return $log_caller;
    }

    public static function log($log_msg, $log_data = null, $log_level = self::DEBUG, $caller_stack_idx = 1)
    {
        if (\Config::$ENABLE_LOG === false) {
            return;
        }

        if (self::$LOG_LEVEL[$log_level] < \Config::$LIMIT_LOG_LEVEL) {
            return;
        }

        if (self::$disabled === true) {
            return;
        }

        $iDebugOption = \Config::$ENABLE_LOG_TRACE_ARGS === true ? DEBUG_BACKTRACE_PROVIDE_OBJECT : DEBUG_BACKTRACE_IGNORE_ARGS;
        $back_trace = debug_backtrace($iDebugOption);

        $log_caller = self::getLogCaller($back_trace, $caller_stack_idx);

        $log_contents = self::_buildLogContents($log_msg, $log_data, $log_level, $log_caller, $back_trace, $caller_stack_idx);

        // to view
        if (\Config::$ENABLE_VIEW_LOG === true && self::$view_disabled === false && !empty($log_contents)) {
            self::_writeFileLog($log_contents);
        }

        // to file
        if (\Config::$ENABLE_FILE_LOG === true && self::$file_disabled === false && !empty($log_contents)) {
            $path = PATH_TEMP . "/log/{$log_caller}/" . date('Ymd') . '/' . date('H');
            if (!empty(\Config::$WHITE_FILE_LOG_CALLER)) {
                $log_caller_split = explode('/', $log_caller);
                if (in_array($log_caller_split[0], \Config::$WHITE_FILE_LOG_CALLER)) {
                    File::appendToFile($path, $log_contents);
                }
            } else {

                File::appendToFile($path, $log_contents);
            }
        }

        // to db
        if (\Config::$ENABLE_DB_LOG === true && self::$db_disabled === false) {
            $model = CommonLog::getInstance(null, 'logger');
            if (is_object($log_data) === true) {
                $log_data = (array)$log_data;
            }
            if (is_array($log_data) && !empty($log_data)) {
                $log_data = json_encode($log_data);
            }
            $db_data = array(
                'log_level'   => $log_level,
                'log_msg'     => $log_msg,
                'server_host' => gethostname()
            );

            $trace_id = self::getTraceId();
            if (empty($trace_id)) {
                self::setTraceId(self::genTraceId());
                $trace_id = self::getTraceId();
            }
            $db_data['trace_id'] = $trace_id;

            if (!empty(self::$log_type)) {
                $db_data['log_type'] = self::$log_type;
            }

            if (!empty($log_data)) {
                $db_data['log_data'] = $log_data;
            }

            if (!empty($log_caller)) {
                $db_data['log_caller'] = $log_caller;
            }

            if (!empty($_SERVER['REMOTE_ADDR'])) {
                $db_data['client_ip'] = $_SERVER['REMOTE_ADDR'];
            }

            $model->add($db_data);
        }
    }

    private static function _writeFileLog($log_contents)
    {
        if (PHP_CLI === false) {
            echo "<pre style='position: relative;z-index: 100000;'>{$log_contents}</pre>";
        } else {
            echo $log_contents;
        }
    }

    private static function _buildLogContents($message, $data, $level, $log_caller, $back_trace, $caller_stack_idx)
    {
        $time = date('Y-m-d H:i:s');
        $eol = PHP_EOL;

        if (PHP_CLI === true) {
            $log = "[{$time} {$level}][CLI][{$log_caller}] {$message}{$eol}";
        } else {
            $log = "[{$time} {$level}][WEB][{$log_caller}][{$_SERVER['REMOTE_ADDR']}] {$message}{$eol}";
        }

        if (!empty($data)) {
            $log .= ('Log Data : ' . print_r($data, true) . $eol);
        }

        if (\Config::$ENABLE_LOG_TRACE === true) {
            for ($i = 0; $i < $caller_stack_idx; $i++) {
                array_shift($back_trace);
            }

            $log .= ('Backtrace : ' . print_r($back_trace, true) . $eol);
        }

        if (empty($log)) {
            return null;
        }

        return $log;
    }
}