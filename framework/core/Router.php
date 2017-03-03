<?php
namespace framework\core;

use framework\exception\RouterException;
use framework\exception\SystemException;
use framework\library\Security;

class Router
{
    private static $instance = null;

    private function __construct()
    {
    }

    /**
     * @throws RouterException
     * @return Router
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function route()
    {
        if (defined('ROUTER_INITIALIZED') && ROUTER_INITIALIZED === true) {
            throw new SystemException('Router is already initialized.', null);
        }
        define('ROUTER_INITIALIZED', true);

        $request = $this->_createRequest();
        if (\Config::$ENABLE_XSS_FILTER === true) {
            $params = $this->_filterParams($request->getMethod());
            $request->setParams($params);
        }

        return $request;
    }

    private function _createRequest()
    {
        $path = $_SERVER['PATH_INFO'];
        if ($path == '/') {
            $controller_name = '\\controller\\Index';
            $action_name = 'index';
        } else {
            if ($path[strlen($path) - 1] === '/') {
                $path = substr($path, 0, strlen($path) - 1);
            }
            $path_list = explode('/', substr($path, 1));
            $uri_cnt = count($path_list);
            if ($uri_cnt === 1) {
                $controller_name = "\\controller\\" . ucfirst($path_list[0]);
                $action_name = 'index';
            } else {
                list($controller_name, $action_name) = $this->_buildController($path_list, $uri_cnt);

                if (!method_exists($controller_name, $action_name)) {
                    list($controller_name, $action_name) = $this->_buildController($path_list, $uri_cnt + 1);
                }
            }
        }

        return Request::getInstance($controller_name, $action_name);
    }

    private function _buildController($path_list, $uri_cnt)
    {
        $path_cnt = $uri_cnt - 2;
        $controller_path = '';
        for ($i = 0; $i < $path_cnt; $i++) {
            $controller_path .= "\\{$path_list[$i]}";
        }
        $path_list[$path_cnt] = ucfirst($path_list[$path_cnt]);
        $controller_name = "\\controller{$controller_path}\\{$path_list[$path_cnt]}";
        $action_idx = $path_cnt + 1;
        $action_name = isset($path_list[$action_idx]) ? $path_list[$action_idx] : 'index';

        return array($controller_name, $action_name);
    }

    private function _filterParams($method)
    {
        $params = array();
        $tmp_params = array();
        if ($method === METHOD_GET) {
            $tmp_params = $_GET;
        } elseif ($method === METHOD_POST) {
            $tmp_params = $_POST;
        }
        unset($_GET);
        unset($_POST);
        unset($_REQUEST);

        foreach ($tmp_params as $key => $val) {
            $params[$key] = Security::filterXss($val);
        }

        return $params;
    }
}