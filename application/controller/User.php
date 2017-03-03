<?php

namespace controller;

use middleware\model\MovieMatchChoice;

class User extends Hovits
{
    public function login()
    {
        if (\service\User::isLogin()) {
            $this->redirect('/', '이미 로그인되어 있습니다.');
        }
        $this->setLayout('default');
    }

    public function loginAction()
    {
        list($email, $password) = $this->validateParams(array('email', 'password'));

        $ret = \service\User::login($email, $password);
        $match_choice_model = MovieMatchChoice::getInstance();
        if ($ret === true) {
            $user_pk = \service\User::getUserPk();
            $exists_choice = $match_choice_model->exist(compact('user_pk'));
            if ($exists_choice) {
                $this->redirect('/');
            } else {
                $this->redirect('/tutorial');
            }
        } elseif ($ret === false) {
            $this->back('로그인을 실패하였습니다.(아이디/패스워드 정보 불일치)');
        } elseif ($ret === -1) {
            $ret = \service\User::register($email, $password);
            if ($ret === true) {
                $ret = \service\User::login($email, $password);
                if ($ret === true) {
                    $user_pk = \service\User::getUserPk();
                    $exists_choice = $match_choice_model->exist(compact('user_pk'));
                    if ($exists_choice) {
                        $this->redirect('/', '자동으로 회원가입/로그인 되었습니다.');
                    } else {
                        $this->redirect('/tutorial', '자동으로 회원가입/로그인 되었습니다.');
                    }
                } else {
                    $this->redirect('/', '자동으로 회원가입 되었습니다. 로그인하세요.');
                }
            } elseif ($ret === false) {
                $this->back('회원가입을 실패하였습니다.');
            } else {
                $this->back('로그인을 실패하였습니다.');
            }
        }
    }

    public function logout()
    {
        \service\User::logout();
        $this->redirect('/');
    }

    public function register()
    {
        if (\service\User::isLogin()) {
            $this->redirect('/', '로그아웃한 후 가입해 주세요.');
        }
        $this->setLayout('default');
    }

    public function findPassword()
    {
        $this->setLayout('default');
    }
}