<?php
namespace controller;

use framework\base\Controller;
use framework\exception\ApplicationException;
use library\Account;
use middleware\library\Menu;

class AdminBase extends Controller
{
    private $bMainPage = false;

    protected function setMainPage()
    {
        $this->bMainPage = true;
    }

    public function __before()
    {
        //check login
        Account::checkLogin();

        //check auth
        $this->_checkMenuPermission();

        $this->_setAdminLayout();

        $this->_setCommonResource();
    }

    private function _checkMenuPermission()
    {
        $sRequestUri = $_SERVER['SCRIPT_URL'];

        Account::checkMenuPermission($sRequestUri);
    }

    private function _setCommonResource()
    {
        $this->setExternalJquery();
        $this->setExternalBootStrap();
        $this->addCss('admin_layout');
        $this->addJs('util');
    }

    /**
     * 상속관계에서 제이쿼리만 import 시키고 싶을때를 위해 따로 메서드로 분리함.
     */
    public function setExternalJquery()
    {
        $this->addExternalJs('//code.jquery.com/jquery-1.11.2.min.js');
    }

    public function setExternalBootStrap()
    {
        $this->addExternalCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css');
        $this->addExternalCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css');
        $this->addExternalJs('//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js');
    }

    private function _setAdminLayout()
    {
        $this->setLayout('admin');

        $this->_setMenu();

        $this->_setLayoutData();
    }

    private function _setLayoutData()
    {
        $aCustomData = array();
        $aCustomData['is_main_page'] = $this->bMainPage;
        $aCustomData['lnb'] = Menu::getLnbHtml();
        $aCustomData['page_header'] = 'HOVITS';
        if ($this->bMainPage === false) {
            $aCustomData['page_header'] = Menu::getCurrentMenu();
            $aCustomData['snb'] = Menu::getSnbHtml();
        }
        $this->setLayoutData($aCustomData);
    }

    private function _setMenu()
    {
        $aMenuList = Menu::getMenuList();

        $sRequestUri = $_SERVER['PATH_INFO'];
        if ($sRequestUri[strlen($sRequestUri) - 1] == '/') {
            $sRequestUri = substr($sRequestUri, 0, -1);
        }
        $bRet = Menu::buildMenuHtml($aMenuList, $sRequestUri);

        if ($bRet === false) {
            throw new ApplicationException('Failed to build menu.');
        } elseif ($bRet === -1) {
            $this->setMainPage();
        }
    }

    public function index()
    {
    }
}