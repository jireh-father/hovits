<?php

class ErrorType
{
    public static $ERR_TYPE_MAP = array(
        1     => 'E_ERROR',
        2     => 'E_WARNING',
        4     => 'E_PARSE',
        8     => 'E_NOTICE',
        16    => 'E_CORE_ERROR',
        32    => 'E_CORE_WARNING',
        64    => 'E_COMPILE_ERROR',
        128   => 'E_COMPILE_WARNING',
        256   => 'E_USER_ERROR',
        512   => 'E_USER_WARNING',
        1024  => 'E_USER_NOTICE',
        2048  => 'E_STRICT',
        4096  => 'E_RECOVERABLE_ERROR',
        8192  => 'E_DEPRECATED',
        16384 => 'E_USER_DEPRECATED',
        30719 => 'E_ALL',
    );

    public static $LOG_LEVEL_MAP = array(
        1     => 'error',
        2     => 'warning',
        4     => 'error',
        8     => 'notice',
        16    => 'error',
        32    => 'warning',
        64    => 'error',
        128   => 'warning',
        256   => 'error',
        512   => 'warning',
        1024  => 'notice',
        2048  => 'critical',
        4096  => 'error',
        8192  => 'warning',
        16384 => 'warning',
        30719 => 'error',
    );
}