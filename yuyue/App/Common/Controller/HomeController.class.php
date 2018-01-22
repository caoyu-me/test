<?php
/**
 *
 * 版权所有：xxx
 * 作    者：曹宇
 * 日    期：2017-11-22
 * 版    本：1.0.0
 * 功能说明：前台的公共调去库。
 *
 **/

namespace Common\Controller;

use Think\Controller;

class HomeController extends BaseController
{
    protected $Users_model;
    public $users;
    public function _initialize()
    {
        parent::_initialize();
        $this->Users_model=M('users');
            //登录用户的信息
        /**
         * 不需要登录控制器
         */
        if(session('user_id')){
            $where['id']=session('user_id');

            $user =$this->Users_model->where($where)->find();
            $this->users=$user;
        }
        if (in_array(MODULE_NAME , array("User"))) {
            if($this->users){
                if($this->users['status']==2){
                    alertUrl('您当前帐号已禁用，请联系我们！', U("/Login"));
                }
            }else{
                 alertUrl('您未登录', U("/Login"));
            }
        }
         $this->assign('user',$user);
//        //广告缓存
//        if(empty($adver)){
//            $adver=$this->Adver_model->select();
//            F('adver',$adver);;
//        }
//        $this->assign('adver', $adver);
    }

}