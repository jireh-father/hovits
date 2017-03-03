<?php
namespace controller\setting;

use controller\AdminSetting;
use middleware\library\Redis;

class Menu extends AdminSetting
{
    public function index()
    {
        $this->addCss('vakata-jstree/style.min');
        $this->addJs('vakata-jstree/jstree.min');
        $this->addJs('setting');
    }

    public function save()
    {
        $aMenuJson = json_decode($this->getParam('menu_json'), true);
        if (empty($aMenuJson) === true) {
            echo 'failed';
            exit;
        }
        $aNewMenuList = \middleware\library\Menu::convertMenuStructure($aMenuJson);
        $oRedis = Redis::getInstance();
        $bRet = $oRedis->set(\middleware\library\Menu::KEY_MENU, $aNewMenuList);
        if ($bRet === true) {
            $aMenuUriList = \middleware\library\Menu::filterMenuUriList($aNewMenuList);
            $bRet = $oRedis->set(\middleware\library\Menu::KEY_MENU_URI, $aMenuUriList);
        }

        if ($bRet === true) {
            echo 'success';
        } else {
            echo 'failed';
        }
        exit;
    }

    public function html()
    {
        $aMenuList = \middleware\library\Menu::getMenuList(false);

        if (empty($aMenuList)) {
            $aMenuList = array(
                array(
                    'menu'     => 'Log',
                    'uri'      => '/log',
                    'sub_menu' => array(
                        array(
                            'menu' => 'Log',
                            'uri'  => '/log'
                        ),
                        array(
                            'menu' => 'Trace Log',
                            'uri'  => '/log/trace'
                        )
                    )
                ),
                array(
                    'menu' => 'Notifier',
                    'uri'  => '/notifier'
                ),
                array(
                    'menu' => 'Scheduler',
                    'uri'  => '/scheduler'
                )
            );
        }

        $sTreeHtml = \middleware\library\Menu::buildMenuTreeHtml($aMenuList);

        echo $sTreeHtml;
        exit;
    }
}