<?php
namespace controller\contents\kofic;

use controller\AdminBase;

class People extends AdminBase
{
    public function index()
    {
        $this->setView('contents/kofic_site', array('url' => 'http://www.kobis.or.kr/kobis/business/mast/peop/searchPeopleList.do'));
    }
}