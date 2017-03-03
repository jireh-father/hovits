<?php

namespace library;

use framework\exception\ApplicationException;
use framework\library\Redirect;
use framework\library\Session;
use middleware\library\Redis;
use middleware\library\Menu;

class Account
{
    const KEY_ACCOUNT = 'ACCOUNT';
    const KEY_ROLE = 'ROLE';
    const KEY_PASSWORD = 'PWD';
    const KEY_ACTION = 'ACTION';
    const DEFAULT_ROLE_NAME = 'DEFAULT_ROLE';
    const ALL_PERMISSION = 'ALL';

    const KEY_WHITE_LIST = 'WHITE_LIST';
    const KEY_BLACK_LIST = 'BLACK_LIST';

    const NO_PERMISSION_URI = '/permission';
    //    private static $aDefaultRole = array(
    //        Menu::KEY_MENU => array(
    //            self::KEY_WHITE_LIST => array('/log/*'),
    //            self::KEY_BLACK_LIST => array('/log/common/notifier')
    //        )
    //    );
    private static $aDefaultRole = array(
        Menu::KEY_MENU   => array(
            self::KEY_WHITE_LIST => array(self::ALL_PERMISSION),
        ),
        self::KEY_ACTION => array(
            self::KEY_WHITE_LIST => array(self::ALL_PERMISSION),
        )
    );

    public static function login($id, $pwd)
    {
        $aAccountData = self::getAccountData($id, $pwd);
        if (empty($aAccountData)) {
            return false;
        }

        Session::set(ADMIN_SESSION_ID, $id);
        Session::set(ADMIN_SESSION_ROLE, $aAccountData[Account::KEY_ROLE]);

        return true;
    }

    public static function isLogin()
    {
        return Session::is(ADMIN_SESSION_ID);
    }

    public static function checkLogin()
    {
        if (!self::isLogin()) {
            Redirect::redirect('/login');
        }
    }

    public static function getAccountData($sId, $sPwd)
    {
        $aAccountData = self::getAccount($sId);
        if (empty($aAccountData)) {
            return null;
        }

        if ($sPwd !== $aAccountData[self::KEY_PASSWORD]) {
            return null;
        }

        $aRole = self::_getRole($aAccountData[self::KEY_ROLE]);
        if (empty($aRole) === true) {
            throw new ApplicationException('롤 데이터 얻기 실패', $aAccountData);
        } else {
            $aAccountData = array(self::KEY_ROLE => $aRole);
        }

        return $aAccountData;
    }

    private static function _getAccountKey($sId)
    {
        return self::KEY_ACCOUNT . "|{$sId}";
    }

    public static function registerAccount($sId, $sPwd)
    {
        $oRedis = Redis::getInstance();
        $sKey = self::_getAccountKey($sId);
        self::_initDefaultRole();
        $aAccountData = array(self::KEY_ROLE => self::DEFAULT_ROLE_NAME, self::KEY_PASSWORD => $sPwd);

        $oRedis->set($sKey, json_encode($aAccountData));

        return $aAccountData;
    }

    public static function getAccount($sId)
    {
        $oRedis = Redis::getInstance();
        $sKey = self::_getAccountKey($sId);

        return json_decode($oRedis->get($sKey), true);
    }

    private static function _initDefaultRole()
    {
        $sKey = self::getRoleKey(self::DEFAULT_ROLE_NAME);
        $oRedis = Redis::getInstance();
        $aDefaultRole = json_decode($oRedis->get($sKey), true);
        if (empty($aDefaultRole) === true) {
            $aDefaultRole = self::$aDefaultRole;
            $oRedis->set($sKey, json_encode($aDefaultRole));
        }

        return $aDefaultRole;
    }

    private static function _getRole($sRoleName)
    {
        $sKey = self::getRoleKey($sRoleName);
        $oRedis = Redis::getInstance();
        $aRole = json_decode($oRedis->get($sKey), true);

        return $aRole;
    }

    public static function setRole($sRoleName, $aRollData)
    {
        $sKey = self::getRoleKey($sRoleName);
        $oRedis = Redis::getInstance();

        return $oRedis->set($sKey, $aRollData);
    }

    public static function getRoleKey($sRoleName)
    {
        return self::KEY_ROLE . "|{$sRoleName}";
    }

    public static function checkMenuPermission($sRequestUri)
    {
        if (empty($sRequestUri) === true || $sRequestUri === '/') {
            return;
        }

        $aMenuUriList = Menu::getMenuUriList();
        if (in_array($sRequestUri, $aMenuUriList) === false) {
            return;
        }

        $aRole = Session::get(ADMIN_SESSION_ROLE);
        if (empty($aRole) === true) {
            self::_redirectToNoPermission();
        }

        $mMenu = $aRole[Menu::KEY_MENU];
        if (empty($mMenu) === true) {
            self::_redirectToNoPermission();
        }

        // black list 우선순위가 더 높음
        if (empty($mMenu[self::KEY_BLACK_LIST]) === false) {
            $aBlackList = $mMenu[self::KEY_BLACK_LIST];
            foreach ($aBlackList as $sBlackUri) {
                if (self::_checkMenu($sRequestUri, $sBlackUri) === true) {
                    self::_redirectToNoPermission();
                }
            }
        }

        if (empty($mMenu[self::KEY_WHITE_LIST]) === false) {
            $aWhiteList = $mMenu[self::KEY_WHITE_LIST];
            foreach ($aWhiteList as $sWhiteUri) {
                if ($sWhiteUri === self::ALL_PERMISSION) {
                    return;
                }
                if (self::_checkMenu($sRequestUri, $sWhiteUri) === true) {
                    return;
                }
            }
            self::_redirectToNoPermission();
        }
    }

    private static function _checkMenu($sRequestUri, $sMenu)
    {
        if (empty($sRequestUri) === true || empty($sMenu) === true) {
            return null;
        }
        $sRequestUri = self::_stripLastSlash($sRequestUri);
        $sMenu = self::_stripLastSlash($sMenu);
        if ($sMenu[strlen($sMenu) - 1] === '*') {
            $sMenu = self::_stripLastSlash(substr($sMenu, 0, -1));

            return strpos($sRequestUri, $sMenu) !== false;
        } else {
            return $sMenu === $sRequestUri;
        }
    }

    private static function _redirectToNoPermission()
    {
        Redirect::redirect(self::NO_PERMISSION_URI);
    }

    private static function _stripLastSlash($sUri)
    {
        $sUri = trim($sUri);
        if ($sUri[strlen($sUri) - 1] === '/') {
            $sUri = substr($sUri, 0, -1);
        }

        return $sUri;
    }

    public static function checkActionAuth($sActionName)
    {
        $aRole = Session::get(ADMIN_SESSION_ROLE);
        $mAction = $aRole[self::KEY_ACTION];
        if (empty($mAction) === true) {
            self::_redirectToNoPermission();
        }

        // black list 우선순위가 더 높음
        if (empty($mAction[self::KEY_BLACK_LIST]) === false) {
            $aBlackList = $mAction[self::KEY_BLACK_LIST];
            if (in_array($sActionName, $aBlackList) === true) {
                self::_redirectToNoPermission();
            }
        }

        if (empty($mAction[self::KEY_WHITE_LIST]) === false) {
            $aWhiteList = $mAction[self::KEY_WHITE_LIST];
            if (in_array(self::ALL_PERMISSION, $aWhiteList) === true) {
                return;
            }
            if (in_array($sActionName, $aWhiteList) === false) {
                self::_redirectToNoPermission();
            }
        }
    }

    public static function getAllAccounts()
    {
        $sAllKey = self::_getAccountKey('*');

        $oRedis = Redis::getInstance();
        $aAccountKeys = $oRedis->keys($sAllKey);
        $aAccountList = array();
        foreach ($aAccountKeys as $sAccountKey) {
            $aAccountKey = explode('|', $sAccountKey);
            $aAccountList[$aAccountKey[1]] = json_decode($oRedis->get($sAccountKey), true);
        }

        return $aAccountList;
    }

    public static function getAllRoles()
    {
        $sAllKey = self::getRoleKey('*');

        $oRedis = Redis::getInstance();
        $aRoleKeys = $oRedis->keys($sAllKey);
        $aRoleList = array();
        foreach ($aRoleKeys as $sRoleKey) {
            $aRoleKey = explode('|', $sRoleKey);
            $aRoleList[$aRoleKey[1]] = json_decode($oRedis->get($sRoleKey), true);
        }

        return $aRoleList;
    }

    public static function buildRoleSelectBox($aRoles, $sCurrentRole)
    {
        $sRoleSelectBox = '<select class="form-control" name="role_name">';
        foreach ($aRoles as $sRoleName => $aRole) {
            if ($sCurrentRole === $sRoleName) {
                $sRoleSelectBox .= sprintf('<option value="%s" selected>%s</option>', $sRoleName, $sRoleName);
            } else {
                $sRoleSelectBox .= sprintf('<option value="%s">%s</option>', $sRoleName, $sRoleName);
            }
        }
        $sRoleSelectBox .= '</select>';

        return $sRoleSelectBox;
    }

    public static function setAccount($sAccountName, $aAccountData)
    {
        if (empty($sAccountName) === true || empty($aAccountData) === true) {
            return false;
        }

        $oRedis = Redis::getInstance();

        return $oRedis->set(self::_getAccountKey($sAccountName), $aAccountData);
    }
}