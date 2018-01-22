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
        $pwd=password(I('post.pwd'));
        $url=U("/Login");
        $verify = I('param.verify','');
       
        if (!$this->check_verify($verify,'login')) {
            alertJs("验证码错误");
        }
        if(preg_match('^[\\u4E00-\\u9FA5\\uF900-\\uFA2D\\w]{4,16}$',$username)){
            alertJs("昵称格式错误，请重新输入");
        }

        if(!$username || !$pwd){
             alertUrl('请填写登录信息',$url);
        }else{
             if(preg_match('/0?(13|14|15|17|18)[0-9]{9}/', $username, $matches)){
                 $where['tel'] = $username;
             }else if(preg_match('/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/',$username, $matches)){
                 $where['email'] = $username;
            }else{
                 $where['name'] = $username;
             }

            $user = $this->Users_model->where($where)->find();
              if($user){
                 if($user['pwd']==$pwd){
                      switch($user['status']){
                          //未激活
                          case 0;
                              $data['update_time']=GetNowTime();
                              $data['end_time']=$user['update_time'];
                              $data['ip']= get_client_ip();
                              $this->Users_model->data($data)-> where($where)->save();
                              session('user_id',$user['id']);
                              header("Location: /user");
                              break;

                          case 1;
                                $data['update_time']=GetNowTime();
                                $data['end_time']=$user['update_time'];
                                $data['ip']= get_client_ip();
                                $this->Users_model->data($data)-> where($where)->save();
                                session('user_id',$user['id']);
                                header("Location: /user");
                              break;

                          case 2;
                              alertUrl('当前帐号已禁用，请联系我们了解详情！',$url);
                              break;

                          default;
                              alertUrl('帐号信息错误，请重试！',$url);
                              break;
                      }
                 }else{
                     alertJs('帐号信息错误，请重试！');
                 }
             }else{
                 alertJs('帐号信息错误，请重试！');
             }
        }

    }
    public function logout()
    {
        session('user_id',null);
        $this->redirect('/');

    }
    //验证码
    function check_verify($code, $id = '')
    {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    public function verify()
    {
        $config = array(
            'fontSize' => 16, // 验证码字体大小
            'length' => 4, // 验证码位数
            'imageW' => 110,
            'imageH' => 40,
            'useCurve'=>false,
            'useNoise'    =>    false, // 关闭验证码杂点
        );
        $verify = new \Think\Verify($config);
        $verify->entry('login');
    }
}