<?php
namespace controller\contents\naver;

class NewMapList extends \controller\contents\cgv\NewMapList
{
    public function index()
    {
        parent::index(CONTENTS_PROVIDER_NAVER);
        $this->setView('contents/cgv/newmaplist');
    }
}