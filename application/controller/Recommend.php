<?php

namespace controller;

class Recommend extends Hovits
{
    public function index()
    {
        $this->_checkLogin();
    }
}