<?php
if (PHP_OS === 'Linux') {
    define('PHP_OS_WINDOWS', false);
    define('PHP_OS_LINUX', true);
} else {
    define('PHP_OS_WINDOWS', true);
    define('PHP_OS_LINUX', false);
}

if (PHP_SAPI === 'cli') {
    define('PHP_CLI', true);
    define('PHP_WEB', false);
} else {
    define('PHP_CLI', false);
    define('PHP_WEB', true);
}

/**
 * DB DSN FIELDS
 */
define('KEY_DSN_DB_TYPE', 'DB_TYPE');
define('KEY_DSN_HOST', 'HOST');
define('KEY_DSN_PORT', 'PORT');
define('KEY_DSN_DB_NAME', 'DB_NAME');
define('KEY_DSN_USERNAME', 'USERNAME');
define('KEY_DSN_PASSWORD', 'PASSWORD');
define('KEY_DSN_OPTION', 'OPTION');

define('METHOD_GET', 'GET');
define('METHOD_POST', 'POST');

define('LOG_LEVEL_CRITICAL', 'CRITICAL');
define('LOG_LEVEL_ERROR', 'ERROR');
define('LOG_LEVEL_WARNING', 'WARNING');
define('LOG_LEVEL_INFO', 'INFO');
define('LOG_LEVEL_DEBUG', 'DEBUG');
define('LOG_LEVEL_ALL', 'ALL');