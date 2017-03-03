<?php
namespace controller;

use framework\base\Controller;

class Test extends Controller{
    public function index()
    {
        $this->addCss('test');
    }
}