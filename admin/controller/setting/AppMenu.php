<?php
namespace controller\setting;

use controller\AdminSetting;
use middleware\library\Menu;
use middleware\library\Redis;

class AppMenu extends AdminSetting
{
    public function index()
    {
        $this->addCss('vakata-jstree/style.min');
        $this->addJs('vakata-jstree/jstree.min');
        $this->addJs('app_menu');
        $this->setView('setting/app_menu');
    }

    public function save()
    {
        $aMenuJson = json_decode($this->getParam('menu_json'), true);
        if (empty($aMenuJson) === true) {
            echo 'failed';
            exit;
        }
        $aNewMenuList = Menu::convertMenuStructure($aMenuJson);
        $oRedis = Redis::getInstance();
        $oRedis->select(1);
        $bRet = $oRedis->set(Menu::KEY_MENU, $aNewMenuList);
        if ($bRet === true) {
            $aMenuUriList = Menu::filterMenuUriList($aNewMenuList);
            $bRet = $oRedis->set(Menu::KEY_MENU_URI, $aMenuUriList);
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
        $aMenuList = Menu::getMenuList(false, 1);

        if (empty($aMenuList)) {
            $aMenuList = array(
                array(
                    'menu' => '박스오피스 / 예매율',
                    'uri'  => '/boxOffice/bookingRatio'
                ),
                array(
                    'menu' => '박스오피스 / 누적관객',
                    'uri'  => '/boxOffice/totalTicket'
                ),
                array(
                    'menu' => '박스오피스 / 평점',
                    'uri'  => '/boxOffice/userGrade'
                )
            );
        }

        $sTreeHtml = Menu::buildMenuTreeHtml($aMenuList);

        echo $sTreeHtml;
        exit;
    }
}