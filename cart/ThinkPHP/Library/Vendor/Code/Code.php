<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zhuanghui
// +----------------------------------------------------------------------


class Code
{
    public $value; //参数
    public $timer; // 规定的时间（60）
    public $type; // 类型
    public $data; //配置值
    private $code;

    public function __construct($value, $time, $type, $data)
    {
        /* 基础设置 */
        //开启session
        Session_start();
        $this->value = $value;
        $this->timer = $time;
        $this->type = $type;
        $this->data = $data;
        $this->code= $this->createSMSCode();
//        dump( $this);
//        die;


    }
    // 18106172122,123545|(123)
    // 获取验证码
    public function GetCode()
    {

        if(strpos($this->value,'@')){
            $stremail=str_replace('.','_',$this->value);

            if (session($stremail)) {
 ;
                //拆分session
                $arr=explode('|',session($stremail));
                //验证码
                $value=$arr[0];
                //保存验证码时时间
                $timer=$arr[1];
                //当前时间  保存时间 过期了
//            dump($this->timer);
//            dump($timer);
//            dump(time());
//            var_dump(time()- $timer > $this->timer);
//            die;
                if(time()- $timer > $this->timer){

                    //大于设置时间
                    return $this->SetCode();
                }else{
                    //小于设置时间
                    //1:在指定时间内不能重复发送
                    return $arr=array('err'=>1);
                }
            } else {

                return $this->SetCode();
            }
        }else{
            if (session($this->value)) {

                //拆分session
                $arr=explode('|',session($this->value));

                //验证码
                $value=$arr[0];
                //保存验证码时时间
                $timer=$arr[1];
                //当前时间  保存时间 过期了
//            dump($this->timer);
//            dump($timer);
//            dump(time());
//            var_dump(time()- $timer > $this->timer);
//            die;
                if(time()- $timer > $this->timer){

                    //大于设置时间
                    return $this->SetCode();
                }else{
                    //小于设置时间
                    //1:在指定时间内不能重复发送
                    return $arr=array('err'=>1);
                }
            } else {

                return $this->SetCode();
            }
        }


    }
    //获取设置
    public function SetCode()
    {

        switch ($this->type){
            case 'email';
                return $this->GetEmailCode();
                break;

            case 'tel';
                return $this->GetTelCode();
                break;

            default;
                //2:当前请求类型错误
                return $arr=array('err'=>2);
                break;
        }
    }
    //获取邮箱验证码
    private function GetEmailCode()
    {
        $data=$this->data;
        Vendor('phpmailer.PHPMailerAutoload','','.php');

        $stremail=str_replace('.','_',$this->value);
        //空session
        $_SESSION[$stremail]='|'.time();
        //session($stremail,'|'.time());
        //   set_time_limit(0);//设置脚本执行超时时间
        $mail = new PHPMailer;
        //https://
        //邮件服务器信息配置
        $mail -> ISSMTP();  //设置邮件发送协议 smtp|pop3|imap
        $mail -> CharSet = $data['charset'];  //设置邮件编码 $data['CharSet']
        $mail -> SMTPSecure = $data['smtpsecure'];  // SMTP 安全协议
        $mail -> Port = $data['prot'];          // 邮件端口
        $mail -> Host = $data['host']; // 使用的邮件服务器

        $mail -> SMTPAuth = true;  //设置phpmail发送邮件是否需要验(username&password)
        if($mail -> SMTPAuth){
            $mail -> Username = $data['name'];
            $mail -> Password = $data['pwd'];//邮箱的授权密码
        }
        $mail -> From = $data['from'];    //来源from
        $mail -> IsHTML(true); // 是否发送html邮件

        //$mail->AddAttachment('./email.txt','email.txt'); // 添加附件,并指定名称

        //发送邮件信息
        $mail -> Addaddress($data['addaddress']); // 收件人
        $mail-> FromName = $data['fromname'];//发送人姓名
        $mail -> Subject = $data['subject'];// 标题
        $content = str_replace('@',$this->code,$data['body']);
        // 内容
        $mail -> Body = str_replace('#',$this->value,$content);

        if($mail->send()){
            //echo '发送成功';
            //0:成功
            //保存session
            $_SESSION[$stremail]=$this->code.'|'.time();
//            session($stremail,$this->code.'|'.time());
              return $arr=array('err'=>0,'invalid'=>$this->timer);
        }else{
            //3:邮箱发送失败
            return $arr=array('err'=>3,'mess'=>$mail->ErrorInfo);
            //echo $mail->ErrorInfo;
        }
    }
    //获取短信验证码
    private function GetTelCode()
    {
        //空session
//        session($this->value,'|'.time());
        $_SESSION[$this->value]='|'.time();
        $data=$this->data;
        //企业ID $userid
        $userid = $data['userid'];
//用户账号 $account
        $account =  $data['account'];
//用户密码 $password
        $password = $data['password'];
//发送到的目标手机号码 $mobile
        $mobile = $data['tel'];
//短信内容 $content
        $content = str_replace('@',$this->code,$data['content']);
//发送短信（其他方法相同）
        $gateway = "http://114.113.154.5/sms.aspx?action=send&userid={$userid}&account={$account}&password={$password}&mobile={$mobile}&content={$content}&sendTime=";
        $result = file_get_contents($gateway);
        $xml = simplexml_load_string($result);
//        echo "返回状态为：".$xml->returnstatus."<br>";
//        echo "返回信息：".$xml->message."<br>";
//        echo "返回余额：".$xml->remainpoint."<br>";
//        echo "返回本次任务ID：".$xml->taskID."<br>";
//        echo "返回成功短信数：".$xml->successCounts."<br>";

        if(trim($xml->returnstatus)=='Success'){
            //0:成功
            //存储session
//           session($this->value,$this->code.'|'.time());
           $_SESSION[$this->value]=$this->code.'|'.time();
           return $arr=array('err'=>0,'invalid'=>$this->timer);
        }else{
            //4:短信发送失败
            return $arr=array('err'=>4);
        }

    }
    //默认生成四位的随机短信验证码。
    // 生成短信验证码
    private function createSMSCode($length = 6){

        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);

    }
}