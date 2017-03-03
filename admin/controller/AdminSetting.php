<?php
namespace controller;

use middleware\library\Menu;

class AdminSetting extends AdminBase
{
    private static $aSettingMenu = array(
        array(
            'menu' => 'Account',
            'uri'  => '/setting/account'
        ),
        array(
            'menu' => 'Menu',
            'uri'  => '/setting/menu'
        ),
        array(
            'menu' => 'Role',
            'uri'  => '/setting/role'
        ),
        array(
            'menu' => 'App Menu',
            'uri'  => '/setting/appMenu'
        )
    );

    public function __before()
    {
        parent::__before();

        $sSnbHtml = Menu::buildSnbHtml(self::$aSettingMenu);
        $aCustomData = $this->getLayoutData();
        $aCustomData['snb'] = $sSnbHtml;
        $aCustomData['page_header'] = Menu::getCurrentMenu();
        $this->setLayoutData($aCustomData);
    }
}