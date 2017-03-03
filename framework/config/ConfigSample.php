<?php

class Config
{
    /**
     * LOG MODE
     */
    public static $ENABLE_LOG = true;

    public static $ENABLE_FILE_LOG = true;
    public static $ENABLE_DB_LOG = true;
    public static $ENABLE_VIEW_LOG = true;

    public static $LIMIT_LOG_LEVEL = LOG_LEVEL_ALL;

    public static $ENABLE_REQUEST_LOG = true;
    public static $ENABLE_SQL_SLOW_QUERY_LOG = true;
    public static $ENABLE_SQL_LOG_BEAUTIFIER = false;

    public static $ENABLE_SQL_BUILDER_CACHE = true;

    public static $ENABLE_MIDDLEWARE = true;

    public static $ENABLE_DEBUG_TWIG = false;

    public static $ENABLE_OPTIMIZE = true;

    public static $LIMIT_SLOW_QUERY_SECONDS = 1;

    /**
     * XSS FILTER
     */
    public static $ENABLE_XSS_FILTER = true;

    /**
     * SESSION
     */
    public static $ENABLE_SESSION = true;

    /**
     * DEFAULT DATABASE
     */
    public static $DEFAULT_DSN = array(
        KEY_DSN_DB_TYPE  => 'mysql',
        KEY_DSN_HOST     => 'xxxxx',
        KEY_DSN_PORT     => '3306',
        KEY_DSN_DB_NAME  => 'xxx',
        KEY_DSN_USERNAME => 'xxx',
        KEY_DSN_PASSWORD => 'xxxx',
        KEY_DSN_OPTION   => null,
    );
}
