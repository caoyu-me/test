<?php
namespace Home\Controller;

class ResetController extends ComController
{
    protected $Users_model;
    public function _initialize()
    {
        parent::_initialize();
        $this->Users_model = M("users");
    }
    public function Index()
    {
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
            $this->ajaxReturn(array('err'=>0,'msg'=>'您输入的验证码错误，请重新输入'));
        }

        if($time+$invalid <time()){
            $this->ajaxReturn(array('err'=>0,'msg'=>'您输入的验证码已过期，请重新输入'));
        }
        if(!I('param.pwd')){
            $this->ajaxReturn(array('err'=>0,'msg'=>'密码不能为空'));
        }
//        /^(\w){6,20}$/ 6-21字母和数字组成，不能是纯数字或纯英文
        if(! preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/',I('post.pwd'))){
            $this->ajaxReturn(array('err'=>0,'msg'=>'密码不符合规则'));
         }
        $newpwd=password(I('param.pwd'));
        $result=$this->Users_model->data(array('pwd'=>$newpwd))->where(array('id'=>$user['id']))->save();

        if($result !== false){
            session('user_id',null);
            session('_'.I('post.data_val'),null);
            $this->ajaxReturn(array('err'=>1,'msg'=>'修改成功，请重新登录！'));
        }else{
            $this->ajaxReturn(array('err'=>0,'msg'=>'修改失败，请重试'));
        }
    }
    public function ajax(){
        if(IS_AJAX){
            $value=I('post.value');
            if(preg_match('/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/', $value)) {
                $where['email']=$value;
            }elseif (preg_match('/0?(13|14|15|17|18)[0-9]{9}/', $value)){
                $where['tel']=$value;
            }
            $verify = I('param.verify','');

            if (!$this->check_verify($verify,'reset')) {
                $this->ajaxReturn(array('err'=>0,'msg'=>'验证码错误！'));
            }
            $num=$this->Users_model->where($where)->find();
            if($num){
                // 存在
                $this->ajaxReturn(array('err'=>1));
            }else{
                // 不存在
                $this->ajaxReturn(array('err'=>0,'msg'=>'您尚未注册！'));
            }

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
            'imageW' => 120,
            'imageH' => 40,
            'useCurve'=>false,
            'useNoise'    =>    false, // 关闭验证码杂点

        );
        $verify = new \Think\Verify($config);
        $verify->entry('reset');
    }
}