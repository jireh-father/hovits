<?php

namespace middleware\library;

use framework\exception\ApplicationException;
use framework\library\Session;

class Menu
{
    const KEY_MENU = 'MENU';
    const KEY_MENU_URI = 'MENU_URI';

    private static $aMenuList;
    private static $sCurrentMenu;
    private static $sLnbHtml;
    private static $sSnbHtml;
    private static $sRequestUri;

    public static function getMenuList($bUseSession = true, $db_number = 0)
    {
        if ($bUseSession === true) {
            $aMenuList = Session::get(ADMIN_SESSION_MENU . $db_number);
        }
        if (empty($aMenuList) === true) {
            $oRedis = Redis::getInstance();
            $oRedis->select($db_number);
            $sMenuJson = $oRedis->get(self::KEY_MENU);

            if (empty($sMenuJson) === true) {
                return null;
            }

            $aMenuList = json_decode($sMenuJson, true);
            if (empty($aMenuList) === true) {
                throw new ApplicationException('메뉴 Json 구조 이상함', $sMenuJson);
            }
            Session::set(ADMIN_SESSION_MENU . $db_number, $aMenuList);
        }
        return $aMenuList;
    }

    public static function getMenuUriList($bUseSession = true, $db_number = 0)
    {
        if ($bUseSession === true) {
            $aMenuUriList = Session::get(ADMIN_SESSION_MENU_URI . $db_number);
        }
        if (empty($aMenuUriList) === true) {
            $oRedis = Redis::getInstance();
            $oRedis->select($db_number);
            $sMenuJson = $oRedis->get(self::KEY_MENU_URI);

            if (empty($sMenuJson) === true) {
                return null;
            }

            $aMenuUriList = json_decode($sMenuJson, true);
            if (empty($aMenuUriList) === true) {
                throw new ApplicationException('메뉴 URI Json 구조 이상함', $sMenuJson);
            }

            Session::set(ADMIN_SESSION_MENU_URI . $db_number, $aMenuUriList);
        }

        return $aMenuUriList;
    }

    public static function getCurrentMenu()
    {
        return self::$sCurrentMenu;
    }

    public static function getLnbHtml()
    {
        return self::$sLnbHtml;
    }

    public static function getSnbHtml()
    {
        return self::$sSnbHtml;
    }


    public static function buildMenuHtml($aMenuList, $sRequestUri, $no_lnb = false)
    {
        if (empty($aMenuList) === true) {
            return null;
        }

        self::_init($aMenuList, $sRequestUri);

        $iSelected = self::_buildLnbHtml($no_lnb);
        if ($iSelected === -1) {
            return true;
        }

        if (empty(self::$aMenuList[$iSelected]['sub_menu']) === true) {
            return -1;
        }

        $sSnbHtml = self::buildSnbHtml(self::$aMenuList[$iSelected]['sub_menu']);
        if ($sSnbHtml === false) {
            return false;
        }

        self::$sSnbHtml = $sSnbHtml;

        return true;
    }

    private static function _init($aMenuList, $sRequestUri)
    {
        self::$aMenuList = $aMenuList;
        self::$sRequestUri = $sRequestUri;
        $sCurrentMenu = null;
        $sLnbHtml = null;
        $sSnbHtml = null;
    }

    private static function _checkMenuItem($aMenu)
    {
        if (empty($aMenu) === true || empty($aMenu['menu']) === true || empty($aMenu['uri']) === true) {
            throw new ApplicationException('Menu structure is invalid.', $aMenu);
        }
    }

    private static function _buildLnbHtml($no_lnb = false)
    {
        $sLnbHtml = '';
        $iSelected = -1;

        foreach (self::$aMenuList as $i => $aMenu) {
            self::_checkMenuItem($aMenu);
            if ($no_lnb) {
                $sMenuUri = $aMenu['uri'];
            } else {
                $sMenuUri = self::_getTopDir($aMenu['uri']);
            }
            $bSelected = (strpos(self::$sRequestUri, $sMenuUri) === 0 && empty(self::$sRequestUri) === false);
            if ($bSelected === true) {
                $iSelected = $i;
                self::$sCurrentMenu = $aMenu['menu'];
            }
            $sLnbHtml .= self::buildMenuItemTag($aMenu['uri'], $aMenu['menu'], $bSelected);
        }

        self::$sLnbHtml = $sLnbHtml;

        return $iSelected;
    }

    private static function _getTopDir($sDir)
    {
        $aDir = explode('/', $sDir);

        return "/{$aDir[1]}";
    }

    public static function buildSnbHtml($aSubMenuList, $bDepth = 0)
    {
        if (empty($aSubMenuList) === true) {
            return false;
        }
        $sSnbHtml = '';
        foreach ($aSubMenuList as $aSubMenu) {
            self::_checkMenuItem($aSubMenu);
            $sMenuUri = $aSubMenu['uri'];
            $sMenu = $aSubMenu['menu'];
            $bActive = $bSelected = self::$sRequestUri === $sMenuUri;
            $sSubSnbHtml = '';
            if ($bSelected === true) {
                self::$sCurrentMenu = $sMenu;
            }
            if (empty($aSubMenu['sub_menu']) === false) {
                $sSubSnbHtml = self::buildSnbHtml($aSubMenu['sub_menu'], $bDepth + 1);
                if (strpos($sSubSnbHtml, 'active') > -1) {
                    $bActive = false;
                }
            }
            $sSnbHtml .= self::buildMenuItemTag($sMenuUri, $sMenu, $bActive, $bDepth);
            $sSnbHtml .= $sSubSnbHtml;
        }

        return $sSnbHtml;
    }

    public static function buildMenuItemTag($sUri, $sMenu, $bActive = false, $bDepth = 0)
    {
        $sActiveClass = $bActive === true ? ' class="active"' : '';

        return sprintf('<li%s%s><a href="%s">%s</a></li>', $sActiveClass, self::_buildMargin($bDepth), $sUri, $sMenu);
    }

    private static function _buildMargin($bDepth)
    {
        if ($bDepth < 1) {
            return '';
        }
        $iMarginPx = $bDepth * 20;

        return sprintf(' style="margin-left: %spx"', $iMarginPx);
    }

    public static function filterMenuUriList($aMenuList)
    {
        $aMenuUriList = array();
        foreach ($aMenuList as $i => $aMenu) {
            $aMenuUriList[] = $aMenu['uri'];
            if (empty($aMenu['sub_menu']) === false) {
                $aMenuUriList = array_merge($aMenuUriList, self::filterMenuUriList($aMenu['sub_menu']));
            }
        }

        return $aMenuUriList;
    }

    public static function buildMenuTreeHtml($aMenuList)
    {
        $sTreeHtml = '<ul>';
        foreach ($aMenuList as $aMenu) {
            $sClass = '';
            if (empty($aMenu['sub_menu']) === false) {
                $sClass = ' class="jstree-open"';
            }
            $sTreeHtml .= "<li{$sClass}>{$aMenu['menu']}::{$aMenu['uri']}";
            if (empty($aMenu['sub_menu']) === false) {
                $sTreeHtml .= self::buildMenuTreeHtml($aMenu['sub_menu']);
            }
            $sTreeHtml .= '</li>';
        }
        $sTreeHtml .= '</ul>';

        return $sTreeHtml;
    }

    public static function convertMenuStructure($aMenuList)
    {
        $aNewMenuList = array();
        foreach ($aMenuList as $aMenu) {
            $aMenuText = explode('::', $aMenu['text']);
            $aTmpMenu = array(
                'menu' => trim($aMenuText[0]),
                'uri'  => trim($aMenuText[1])
            );
            if (empty($aMenu['children']) === false) {
                $aTmpMenu['sub_menu'] = self::convertMenuStructure($aMenu['children']);
            }
            $aNewMenuList[] = $aTmpMenu;
        }

        return $aNewMenuList;
    }
}