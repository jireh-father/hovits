<?php
namespace controller\contents\kofic;

use controller\AdminBase;

class HtmlViewer extends AdminBase
{
    public function index()
    {
        $this->addJs('util/html_viewer');
        $this->setView('util/html_viewer', array('include_kofic_css' => true));
    }
}