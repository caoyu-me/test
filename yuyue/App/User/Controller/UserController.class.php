<?php
namespace User\Controller;
class UserController extends ComController
{
    protected $Users_model;
    public function _initialize(){
        parent::_initialize();
        $this->Users_model = M("users");

    }
    public function Index()
    {
        $user=$this->users;
        if($user['birth']){
            $birth=explode('-','1995-5-9');

            $this->assign('birth',$birth);
        }


        $this->display('user');
    }
    public function edit()
    {
        if(IS_AJAX){
            $rules = array(
                array('name','','用户名已存在！',0,'unique',2),
                array('full_name','require','真实姓名不能为空！'),
                array('tel','','手机号已存在！',0,'unique',2),
                array('email','','邮箱已存在！',0,'unique',2),
                array('id_code','','身份证号已存在！',0,'unique',2),
            );
            $user_model = M('users');

            if (!$user_model->validate($rules)->create(I('post.'))) {
                $this->ajaxReturn(array('err'=>0 ,'msg'=>$user_model->getError()));
                exit;
            }

            $user=$this->users;
            //修改
            $result = $this->Users_model->where(array('id'=>$user['id']))->data($_POST)->save();

             if($result){
                 $this->ajaxReturn(array('err'=>1));
             }else{
                 $this->ajaxReturn(array('err'=>0,'msg'=>'系统错误，请稍后再试！'));
             }
        }

    }

     
}