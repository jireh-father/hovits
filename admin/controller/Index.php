<?php
namespace controller;

class Index extends AdminBase
{
    public function __before()
    {
        $this->setMainPage();
        parent::__before();
    }

    public function index()
    {
        $this->setView('main');
    }
}