<?php
namespace controller\setting;

use controller\AdminSetting;
use framework\library\Redirect;
use library\Account;
use middleware\library\Menu;

class Role extends AdminSetting
{
    public function index()
    {
        Account::checkActionAuth('auth');
        $aRoles = Account::getAllRoles();
        $aMenuUriList = array_unique(Menu::getMenuUriList(false));

        $this->addJs('setting');
        $this->setViewData(compact('aRoles', 'aMenuUriList'));
    }

    public function set()
    {
        $sRoleName = $this->getParam('role_name');

        if (empty($sRoleName) === true) {
            Redirect::back('role_name 값은 필수입니다.');
        }

        $aRoleData = $this->_buildRoleData();
        $bRet = Account::setRole($sRoleName, $aRoleData);
        if ($bRet === true) {
            Redirect::redirect('/setting/role', '성공 : 재로그인해야 반영됩니다.');
        } else {
            Redirect::back('실패');
        }
    }

    private function _buildRoleData()
    {
        $aRoleData = array();
        $aRoleData[Menu::KEY_MENU] = array();
        $aRoleData[Account::KEY_ACTION] = array();

        $sWhiteActions = $this->getParam('white_action');
        $sBlackActions = $this->getParam('black_action');
        $aWhiteMenuUris = $this->getParam('white_menu_uri');
        $aBlackMenuUris = $this->getParam('black_menu_uri');

        if (empty($sWhiteActions) === false) {
            $aWhiteActions = $this->_explodeTrimUnqArray($sWhiteActions);
            $aRoleData[Account::KEY_ACTION][Account::KEY_WHITE_LIST] = $aWhiteActions;
        }

        if (empty($sBlackActions) === false) {
            $aBlackActions = $this->_explodeTrimUnqArray($sBlackActions);
            $aRoleData[Account::KEY_ACTION][Account::KEY_BLACK_LIST] = $aBlackActions;
        }

        if (empty($aWhiteMenuUris) === false) {
            $aMenuUris = array();
            foreach ($aWhiteMenuUris as $i => $sMenuUri) {
                $sMenuUri = trim($sMenuUri);
                if ($this->getParam('white_children_all_' . $i) === '*') {
                    $sMenuUri .= '*';
                }
                $aMenuUris[] = $sMenuUri;
            }
            $aMenuUris = array_unique($aMenuUris);
            $aRoleData[Menu::KEY_MENU][Account::KEY_WHITE_LIST] = $aMenuUris;
        }

        if (empty($aBlackMenuUris) === false) {
            $aMenuUris = array();
            foreach ($aBlackMenuUris as $i => $sMenuUri) {
                $sMenuUri = trim($sMenuUri);
                if ($this->getParam('black_children_all_' . $i) === '*') {
                    $sMenuUri .= '*';
                }
                $aMenuUris[] = $sMenuUri;
            }
            $aMenuUris = array_unique($aMenuUris);
            $aRoleData[Menu::KEY_MENU][Account::KEY_BLACK_LIST] = $aMenuUris;
        }

        return $aRoleData;
    }

    private function _explodeTrimUnqArray($sData)
    {
        $aData = explode(',', $sData);
        $aData = array_unique($aData);

        return array_map('trim', $aData);
    }
}