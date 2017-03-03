<?php
namespace controller\contents\naver;

class Contents extends \controller\contents\cgv\Contents
{
    public function index()
    {
        parent::index();
        $this->setView('contents/cgv/contents');
    }
}