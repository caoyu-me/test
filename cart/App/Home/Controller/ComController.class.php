<?php
/**
 *
 * 版权所有：xxx
 * 作    者：曹宇
 * 日    期：2017-11-22
 * 版    本：1.0.0
 * 功能说明：个人中心进入判断。
 *
 **/

namespace Home\Controller;

use Common\Controller\HomeController;
use Think\Auth;

class ComController extends HomeController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    /*public function check_login(){
        $flag = false;
        $salt = C("COOKIE_SALT");
        $ip = get_client_ip();
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $auth = cookie('auth');
        $id = session('kid');
        if ($id) {
            $user = M('kehu')->where(array('id' => $id))->find();

            if ($user) {
                if ($auth ==  password($id.$user['kh_name'].$ip.$ua.$salt)) {
                    $flag = true;
                    $this->USER = $user;
                }
            }
        }
        return $flag;
    }*/
}