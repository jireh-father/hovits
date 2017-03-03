<?php
namespace framework\core;

class Request
{
    private static $instance;

    private $method;
    private $action_name;
    private $controller_name;
    private $params = array();

    private function __construct($controller_name, $action_name)
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->action_name = $action_name;
        $this->controller_name = $controller_name;
    }

    public static function getInstance($controller_name = null, $action_name = null)
    {
        if (empty(self::$instance)) {
            self::$instance = new self($controller_name, $action_name);
        }

        return self::$instance;
    }

    public function getControllerName()
    {
        return $this->controller_name;
    }

    public function setControllerName($controller_name)
    {
        $this->controller_name = $controller_name;
    }

    public function getActionName()
    {
        return $this->action_name;
    }

    public function setParam($key, $value)
    {
        return $this->params[$key] = $value;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function getParam($key, $default = null)
    {
        return isset($this->params[$key]) ? $this->params[$key] : $default;
    }

    public function getParams($keys = null, $replace_keys = null)
    {
        if (empty($keys)) {
            return $this->params;
        }

        if (empty($replace_keys)) {
            $replace_keys = $keys;
        }

        $params = array();
        foreach ($keys as $i => $key) {
            if (isset($this->params[$key])) {
                $params[$replace_keys[$i]] = $this->params[$key];
            }
        }

        return $params;
    }

    public function getMethod()
    {
        return $this->method;
    }
}
