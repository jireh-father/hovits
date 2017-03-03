<?php
namespace framework\core;


use framework\base\Controller;
use framework\exception\DispatcherException;
use framework\exception\SystemException;

class Dispatcher
{
    private static $instance = null;
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Controller
     */
    private $controller = null;

    private function __construct($request)
    {
        $this->request = $request;
    }

    public static function getInstance(Request $request)
    {
        if (empty(self::$instance)) {
            self::$instance = new self($request);
        }

        return self::$instance;
    }

    //보안 처리(파라미터, 쿠키, 세션)
    //컨트롤러 생성
    //컨트롤러 전처리
    //컨트롤러 실행
    //  ->컨트롤러 내부: response 헤더(타입) 지정, 자원사용, 옵티마이저 세팅
    //컨트롤러 후처리
    public function dispatch()
    {
        if (defined('IS_DISPATCHED') && IS_DISPATCHED === true) {
            throw new SystemException('Already dispatched!');
        }
        define('IS_DISPATCHED', true);

        $this->_createController($this->request->getControllerName());

        $this->_executeController($this->request->getActionName());
    }

    /**
     * @param $controller_name
     * @return Controller
     * @throws DispatcherException
     */
    private function _createController($controller_name)
    {
        if (!class_exists($controller_name)) {
            $controller_name = '\\middleware' . $controller_name;
            if (!class_exists($controller_name)) {
                throw new SystemException('Controller name is not exist', $controller_name);
            }
            $this->request->setControllerName($controller_name);
        }

        $this->controller = new $controller_name();
    }

    private function _executeController($action_name)
    {
        $this->response = Response::getInstance();
        $controller = $this->controller;
        $controller->__init($this->request, $this->response);
        $controller->__before();
        if (!method_exists($controller, $action_name)) {
            throw new SystemException('Method name is not exist', $action_name);
        }
        $controller->$action_name();
        $controller->__after();
    }

    public function response()
    {
        $this->response->response($this->request);
    }
}