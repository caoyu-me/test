<?php
namespace User\Controller;

class PwdController extends ComController
{

    public function _initialize()
    {
        parent::_initialize();

    }
    public function Index()
    {
        $this->display();
    }
    public function reset(){
        $this->display();
    }
    public function save()
    {
       $user=$this->users;
        //拆分session
        if(strpos(I('post.data_val'),'@')){
            $arr=explode('|',$_SESSION[str_replace('.','_',I('post.data_val'))]);
        }else{
            $arr=explode('|',$_SESSION['_'.I('post.data_val')]);
        }

        //验证码
        $code=$arr[0];
        //发送时间
        $time=$arr[1];
        //验证码
        $data['code']=I('post.code');
        //失效时间
        $invalid=I('post.invalid');
        if($data['code']!==$code ){
            alertJs('您输入的验证码错误，请重新输入');
        }

        if($time+$invalid <time()){
            alertJs('您输入的验证码已过期，请重新输入');
        }
        if(!I('post.pwd')){
            alertJs('原密码必须填');
         }
        if(!I('post.pwd2')){
            alertJs('新密码必须填' );
         }
        if(!I('post.pwd2')==I('post.pwd3')){
            alertJs('两次密码必须相同');
         }
//        /^(\w){6,20}$/ 6-21字母和数字组成，不能是纯数字或纯英文
        if(! preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/',I('post.pwd3'))){
            alertJs('密码不符合规则');
         }
        $password=password(I('post.pwd'));
        if($password!==$user['pwd']){
            alertJs('密码不正确，请重试');
        }

        $newpwd=password(I('post.pwd3'));
        $result=$this->Users_model->data(array('pwd'=>$newpwd))->where(array('id'=>$user['id']))->save();

        if($result !== false){
            session('user_id',null);
            session('_'.I('post'),null);
             alertUrl('修改成功，请重新登录！',U('/login/index'));
         }else{
            alertUrl('修改失败，请重试',U('pwd/index'));
         }
    }
   
}