<?php
/**
 * Created by PhpStorm.
 * User: caoyu-me
 * Date: 2017/11/21
 * Time: 10:24
 * 功能说明：注册
 */
namespace Home\Controller;
use Common\Controller\HomeController;
use Think\Controller;
class RegisterController extends HomeController
{
    protected $Users_model;
    public function _initialize(){
        parent::_initialize();

        $this->Users_model = M("users");
    }
    public function index()
    {
         $this->display();
    }
    //注册
    public function add()
    {
        $user = D('users');
         if(!$user->create()){
            $error=$user->getError();
            alertUrl($error,U('/register'));
        }

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
        //手机或邮箱\
        $tel_code ='/0?(13|14|15|17|18)[0-9]{9}/';
        if(I('post.data_val')==''){
            alertJs('请输入手机号或邮箱');
        }
        if(preg_match('/0?(13|14|15|17|18)[0-9]{9}/', I('post.data_val'))){
            $data['tel']=I('post.data_val');
        } else{
            $data['email']=I('post.data_val');
        }

        //密码
        $data['pwd']=password(I('post.pwd'));
        //未激活
        $data['status']=0;

        //失效时间
        $invalid=I('post.invalid');
//        dump($_SESSION);
//        dump($time);
//        dump($invalid);
//        dump($time+$invalid);
//        dump(time());
//        die;
        if($data['code']!==$code ){
            alertJs('您输入的验证码错误，请重新输入');
        }

        if($time+$invalid <time()){
            alertJs('您输入的验证码已过期，请重新输入');
        }
        $result=$this->Users_model->data($data)->add();
        if($result){
            session('user_id',$result);
            session('_'.I('post'),null);

            alertUrl('注册成功,',U('/user'));
        }else{
            alertUrl('注册失败',U('/register'));
        }

    }
    //邮箱激活
    public function verification()
    {
        $id=I('get.id');
        $time=I('get.time');
        $token=I('get.token');
        $verify = I('param.verify','');

        if (!$this->check_verify($verify,'register')) {
            alertJs("验证码错误");
        }
        if(time() > intval($time)){
            alertJs('您的激活有效期已过，请登录您的帐号重新发送激活邮件');
        }
        $user=$this->Users_model->where(array('id'=>$id))->find();
        if($user['status']==1){
            alertUrl('您已激活,请直接登录',U('/user'));
        }
        $time=$time-60*60*24;
//        $token = md5($data['name'].$data['pwd'].$regtime); //创建用于激活识别码
        if(md5($user['name'].$user['pwd'].$time)==$token){
            session('user_id',$user['id']);
            alertUrl('激活成功',U('/user'));
        }else{
            alertUrl('激活失败，请稍后再试',U('/user'));
        }

    }
    //验证码
    function check_verify($code, $id = '')
    {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
    //验证码前台显示
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
        $verify->entry('register');
    }
    //查询有无重复
    public function ajaxdata(){
        switch (I('post.type')){
            case 'name';
                $where['name']=I('post.name');
                break;
            case 'email';
                $where['email']=I('post.name');
                break;
            case 'tel';
                $where['tel']=I('post.name');
                break;
        }
        $num=$this->Users_model->where($where)->count();
        if($num){
            // 存在
           echo json_encode(array('err'=>1));
        }else{
            // 不存在
           echo  json_encode(array('err'=>0));
        }

    }
    public function email()
    {
        $this->display();
    }

}