<?php

namespace controller;

use service\User;

class Evaluate extends Hovits
{
    public function index()
    {
        $this->_checkLogin();
    }
}