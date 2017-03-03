<?php
namespace framework\boot;

use framework\core\Dispatcher;
use framework\core\Router;

require_once 'Bootstrap.php';

BootstrapWeb::start();

class BootstrapWeb extends Bootstrap
{
    public static function start()
    {
        parent::start();

        /**
         * INIT SYSTEM
         */
        Initializer::initFramework();

        /**
         * RUN ROUTER
         */
        $router = Router::getInstance();

        $request = $router->route();

        /**
         * RUN DISPATCHER AND RESPONSE!
         */
        $dispatcher = Dispatcher::getInstance($request);

        $dispatcher->dispatch();

        $dispatcher->response();

        parent::finish($request);
    }
}