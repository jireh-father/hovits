<?php
namespace service;

use framework\library\Session;
use framework\library\Time;

class User
{
    public static function getUserPk()
    {
        $user_session = Session::get(USER_SESSION_KEY);
        if (empty($user_session)) {
            return null;
        }

        return $user_session['user_pk'];
    }

    public static function isLogin()
    {
        $user_session = Session::get(USER_SESSION_KEY);

        return !empty($user_session);
    }

    public static function login($email, $password)
    {
        if (emptyOr($email, $password)) {
            return false;
        }

        $user_model = \middleware\model\User::getInstance();
        $user_data = $user_model->getRow(array('user_id' => $email));
        if (empty($user_data)) {
            return -1;
        }

        $hashed_password = self::createHashedPassword($password, $user_data['user_pw_salt']);
        if ($hashed_password === $user_data['user_pw']) {
            return self::setUserSession($user_data);
        } else {
            return false;
        }
    }

    public static function logout()
    {
        if(self::isLogin()){
            Session::set(USER_SESSION_KEY, null);
        }
    }

    public static function register($email, $password)
    {
        if (emptyOr($email, $password)) {
            return false;
        }

        $user_model = \middleware\model\User::getInstance();
        $salt = createSalt();
        $user_data = array(
            'user_id'      => $email,
            'user_pw'      => self::createHashedPassword($password, $salt),
            'user_pw_salt' => $salt,
            'insert_time'  => Time::YmdHis()
        );

        return $user_model->add($user_data);
    }

    public static function setUserSession($user)
    {
        if (!$user) {
            return false;
        }

        Session::set(USER_SESSION_KEY, $user);

        return true;
    }

    public static function createHashedPassword($password, $salt)
    {
        $salted_password = $password . $salt;

        return hash('sha256', $salted_password);
    }
}
