<?php
namespace controller\contents\kofic;

use controller\AdminBase;

class Movie extends AdminBase
{
    public function index()
    {
        $this->setView('contents/kofic_site', array('url' => 'http://www.kobis.or.kr/kobis/business/mast/mvie/searchMovieList.do'));
    }
}