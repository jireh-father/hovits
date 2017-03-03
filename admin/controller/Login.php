<?php
namespace controller;

use framework\base\Controller;
use framework\library\Redirect;
use framework\library\Session;
use library\Account;

class Login extends Controller
{
    public function __before()
    {
        $this->addExternalCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css');
        $this->addExternalCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css');
        $this->addExternalJs('//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js');
    }


    public function index()
    {
    }

    public function doLogin()
    {
        $sId = $this->getParam('id');
        $sPwd = $this->getParam('pwd');

        if (!Account::login($sId, $sPwd)) {
            Redirect::back('로그인을 실패했습니다.');
        }

        $this->redirect('/');
    }

    public function register()
    {

    }

    public function logout()
    {
        Session::destroy();
        $this->redirect('/login');
    }

    public function doRegister()
    {
        $sId = $this->getParam('id');
        $sPwd = $this->getParam('pwd');

        if (Account::login($sId, $sPwd)) {
            Redirect::redirect('/', '이미 가입된 계정으로 로그인합니다.');
        }

        $account = Account::getAccount($sId);
        if (!empty($account)) {
            Redirect::back('이미 존재하는 계정입니다.');
        }

        Account::registerAccount($sId, $sPwd);

        if (Account::login($sId, $sPwd)) {
            Redirect::redirect('/', '가입한 계정으로 로그인합니다..');
        } else {
            Redirect::back('가입한 계정으로 로그인 실패!');
        }
    }
}