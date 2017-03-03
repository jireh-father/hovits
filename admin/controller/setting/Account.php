<?php

namespace controller\setting;

use controller\AdminSetting;

/**
 * 메뉴 세팅
 * @package
 * @author 서일근 <igseo@simplexi.com>
 * @version 1.0
 * @since 2015. 04. 17
 */
class Account extends AdminSetting
{
    public function index()
    {
        $aAccounts = \library\Account::getAllAccounts();

        ksort($aAccounts);

        $aRoles = \library\Account::getAllRoles();
        ksort($aRoles);

        $this->addJs('setting');
        $this->setViewData(compact('aAccounts', 'aRoles'));
    }

    public function set()
    {
        $sAccountName = r()->getParam('account_name');
        $sRoleName = r()->getParam('role_name');

        $sUserId = s()->get(GW_SESSION_ID);
        if ($sUserId !== $sAccountName) {
            libAccount::checkActionAuth('auth');
        }

        $aAccountData = array(libAccount::KEY_ROLE => $sRoleName);
        $bRet = libAccount::setAccount($sAccountName, $aAccountData);
        if ($bRet === true) {
            libRedirect::redirect('/setting/account', '성공');
        } else {
            libRedirect::back('변경 실패');
        }
    }
}