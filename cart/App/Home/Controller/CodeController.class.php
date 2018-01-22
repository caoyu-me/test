<?php
namespace Home\Controller;
use Common\Controller\HomeController;
class CodeController extends HomeController
{
    protected $Users_model;
    public function _initialize(){
        parent::_initialize();
        $this->Users_model = M("users");
    }
    public function index()
    {
        $value=I('post.value');
        $type=I('post.type');
        $type2=I('post.type2');
        $value='18106172122@163.com';
        $type='email';
        $type2=intval('1');
            switch ($type){
            case 'email';
                if(preg_match('/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/', $value)){
                    $result= $this->user($value,$type);
                    if($result){
                        echo json_encode($arr=array('err'=>1));
                        return;
                    }
                    $data['charset']=C('EMAIL_CHARSET');
                    $data['smtpsecure']=C('EMAIL_SMTPSECURE');
                    $data['prot']=C('EMAIL_PROT');
                    $data['host']=C('EMAIL_HOST');
                    $data['name']=C('EMAIL_NAME');
                    $data['pwd']=C('EMAIL_PWD');
                    $data['from']=C('EMAIL_FROM');
                    $data['addaddress']= $value;
                    $data['fromname']='乾园大通';
                    $data['subject']=C('EMAIL_SUBJECT');
                    $data['subject']=$data['subject'][$type2];
                    $data['body']= C('EMAIL_REGBODY');
                    $data['body']=$data['body'][$type2];
                    vendor('Code.Code', '' ,'.php');
                    $timer=60;
                    $code = new \code($value,$timer,$type,$data);
                    $result= $code->GetCode();
                    echo json_encode($result);

                }else{
                    //邮箱格式错误
                    echo json_encode(array('err'=>-2));
                }
                break;

            case 'tel';
                if(preg_match('/0?(13|14|15|17|18)[0-9]{9}/', $value)){
                    $result= $this->user($value,$type);
                    if($result){
                        echo json_encode($arr=array('err'=>1));
                        return;
                    }
                    //短信
                    $data['userid']=C('TEL_USERID');
                    $data['account']=C('TEL_ACCOUNT');
                    $data['password']=C('TEL_PASSWORD');
                    $data['content']=C('TEL_REGCONTENT');
                    $data['tel']=$value;
                    $timer=60;
                    vendor('Code.Code', '' ,'.php');
                    $code = new \code($value,$timer,$type,$data);
                    $result= $code->GetCode();

                    echo json_encode($result);
                }else{
                    //手机号码格式错误
                    echo json_encode(array('err'=>-1));
                }

                break;

        }

    }
    private function user($value,$type)
    {
        switch ($type){
            case 'email';
            $where['email']=$value;
            break;
            case 'tel';
            $where['tel']=$value;
            break;
        }
        $user=$this->Users_model->where($where)->find();

        return $user;

    }
}