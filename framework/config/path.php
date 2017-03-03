<?php
/**
 * Framework Path
 */
define('PATH_FW_CONFIG', PATH_FW . '/config');
define('PATH_FW_EXCEPTION', PATH_FW . '/exception');
define('PATH_FW_LIBRARY', PATH_FW . '/library');
define('PATH_FW_BASE', PATH_FW . '/base');

define('PATH_MW_CONFIG', PATH_MW . '/config');
define('PATH_MW_CONTROLLER', PATH_MW . '/controller');
define('PATH_MW_VIEW', PATH_MW . '/view/content');
define('PATH_MW_LAYOUT', PATH_MW . '/view/layout');
define('PATH_MW_BLOCK', PATH_MW . '/view/block');
define('PATH_MW_RESOURCE', PATH_MW . '/resource');
define('PATH_MW_JS', PATH_MW_RESOURCE . '/js');
define('PATH_MW_CSS', PATH_MW_RESOURCE . '/css');
define('PATH_MW_RES_LIB', PATH_MW_RESOURCE . '/lib');

define('PATH_APP_CONFIG', PATH_APP . '/config');
define('PATH_APP_CONTROLLER', PATH_APP . '/controller');
define('PATH_APP_VIEW', PATH_APP . '/view/content');
define('PATH_APP_LAYOUT', PATH_APP . '/view/layout');
define('PATH_APP_BLOCK', PATH_APP . '/view/block');
define('PATH_APP_RESOURCE', PATH_APP . '/resource');
define('PATH_APP_CONSOLE', PATH_APP . '/console');
define('PATH_APP_JS', PATH_APP_RESOURCE . '/js');
define('PATH_APP_CSS', PATH_APP_RESOURCE . '/css');
define('PATH_APP_RES_LIB', PATH_APP_RESOURCE . '/lib');



if (!defined("PATH_FW")) {
    echo 'You have to define PATH_FW in path.php';
    exit;
}

if (!defined("PATH_TEMP")) {
    echo 'You have to define PATH_APP_TEMP in path.php';
    exit;
}

if (!defined("PATH_APP")) {
    echo 'You have to define PATH_APP in path.php';
    exit;
}