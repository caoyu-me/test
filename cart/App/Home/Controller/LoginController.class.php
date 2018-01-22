<?php
/**
 * Created by PhpStorm.
 * User: caoyu-me
 * Date: 2017/11/14
 * Time: 20:28
 */
namespace Home\Controller;
use Common\Controller\HomeController;

use Think\Controller;
class LoginController extends HomeController{
    protected $Users_model;
    public function _initialize(){
        parent::_initialize();

        $this->Users_model = M("users");

    }
    public  function Index()
    {
         $this->display();
    }
    public function login()
    {
        $username=I('post.username');
        $pwd= I('post.pwd');
        $url=U("/Login");
        if(!$username || !$pwd){
             alertUrl('请填写登录信息',$url);
        }else{
            $where['name'] = $username;
            $user = $this->Users_model->where($where)->find();
             dump($user);
             if($user){
              $data['update_time']=GetNowTime();
              $data['end_time']=$user['update_time'];
              $data['ip']= get_client_ip();
              $this->Users_model->data($data)-> where($where)->save();
              session('user_id',$user['id']);
              header("Location: /sell");
                 }else{
                     alertJs('密码，请重试！');
                 }
                 alertJs('帐号信息错误，请重试！');
             }

    }
    public function logout()
    {
        session('user_id',null);
        $this->redirect('/');

    }

}