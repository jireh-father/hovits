<?php
namespace controller\util;

use controller\AdminBase;
use middleware\library\HtmlFormatter;

class HtmlViewer extends AdminBase
{
    public function index()
    {
        $this->addJs('util/html_viewer');
        $this->setView('util/html_viewer');
    }

    public function htmlBeautifier()
    {
        $html = $this->getParam('html', null);
        if (empty($html)) {
            $this->ajaxFail('html 데이터 없음');
        }

        $html = HtmlFormatter::format($html);

        $this->ajaxSuccess('성공', $html);
    }
}