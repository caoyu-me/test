<?php
/**
 *
 * 版权所有：恰维网络<qwadmin.qiawei.com>
 * 作    者：寒川<hanchuan@qiawei.com>
 * 日    期：2015-09-17
 * 版    本：1.0.0
 * 功能说明：模块公共文件。
 *
 **/


function UpImage($callBack = "image", $width = 100, $height = 100, $image = "",$kind,$id)
{

    echo '<iframe scrolling="no" frameborder="0" border="0" onload="this.height=this.contentWindow.document.body.scrollHeight;this.width=this.contentWindow.document.body.scrollWidth;" width=' . $width . ' height="' . $height . '"  src="' . U('Upload/uploadpic', array('Width' => $width, 'Height' => $height, 'BackCall' => $callBack,'kind'=>$kind,'id'=>$id)) . '"></iframe>
         <input type="hidden" ' . 'value = "' . $image . '"' . 'name="' . $callBack . '" id="' . $callBack . '">';
}

function BatchImage($callBack = "image", $width = 100, $height = 100, $image = "")
{
    
    echo '<iframe scrolling="no" frameborder="0" border="0" width=100% onload="this.height=this.contentWindow.document.body.scrollHeight;" src="' . U('Upload/batchpic',
            array('Width' => $width, 'Height' => $height, 'BackCall' => $callBack)) . '"></iframe>
		<input type="hidden" ' . 'value = "' . $image . '"' . 'name="' . $callBack . '" id="' . $callBack . '">';
}


/*
 * 函数：网站配置获取函数
 * @param  string $k      可选，配置名称
 * @return array          用户数据
*/
function setting($k = 'all')
{
    $cache = S($k);
    //如果缓存不为空直接返回
    if (null != $cache) {
        return $cache;
    }
    $data = '';
    $setting = M('setting');
    //判断是否查询全部设置项
    if ($k == 'all') {
        $setting = $setting->field('k,v')->select();
        foreach ($setting as $v) {
            $config[$v['k']] = $v['v'];
        }
        $data = $config;

    } else {
        $result = $setting->where("k='{$k}'")->find();
        $data = $result['v'];

    }
    //建立缓存
    if ($data) {
        S($k, $data);
    }
    return $data;
}

/**
 * 函数：格式化字节大小
 * @param  number $size 字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) {
        $size /= 1024;
    }
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 函数：加密
 * @param string            密码
 * @return string           加密后的密码
 */
function password($password)
{
    /*
    *后续整强有力的加密函数
    */
    return md5('Q' . $password . 'W');

}

/**
 * 随机字符
 * @param number $length 长度
 * @param string $type 类型
 * @param number $convert 转换大小写
 * @return string
 */
function random($length = 6, $type = 'string', $convert = 0)
{
    $config = array(
        'number' => '1234567890',
        'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'string' => 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
        'all' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
    );

    if (!isset($config[$type])) {
        $type = 'string';
    }
    $string = $config[$type];

    $code = '';
    $strlen = strlen($string) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $string{mt_rand(0, $strlen)};
    }
    if (!empty($convert)) {
        $code = ($convert > 0) ? strtoupper($code) : strtolower($code);
    }
    return $code;
}

//获取所有的子级id
function category_get_sons($sid, &$array = array())
{
    //获取当前sid下的所有子栏目的id
    $categorys = M("category")->where("pid = {$sid}")->select();

    $array = array_merge($array, array($sid));
    foreach ($categorys as $category) {
        category_get_sons($category['id'], $array);
    }
    $data = $array;
    unset($array);
    return $data;

}


/**
 * 获取文章url地址
 * url结构：ttp://wwww.qwadmin.com/分类/子分类/子分类/id.html
 * 使用方法：模板中{:articleUrl(array('aid'=>$val['aid']))}
 *
 *
 * @param $data
 * @return $string
 */
function articleUrl($data)
{
    //如果数组为空直接返回空字符
    if (!$data) {
        return '';
    }
    //如果参数错误直接返回空字符
    if (!isset($data['aid'])) {
        return '';
    }

    $aid = (int)$data['aid'];

    //获取文章信息
    $article = M('article')->where(array('aid' => $aid))->find();
    //获取当前内容所在分类
    $category = M('category')->where(array('id' => $article['sid']))->find();
    //获取当前分类
    $categoryUrl = $category['dir'];
    //遍历获取当前文章所在分类的有上级分类并且组合url
    while ($category['pid'] <> 0) {
        $category = M('category')->where(array('id' => $category['pid']))->find();
        $categoryUrl = $category['dir'] . "/" . $categoryUrl;
        //如果上级分类已经无上级分类则退出
    }

    $categoryUrl = __ROOT__ . "/" . $categoryUrl;
    //组合文章url
    $articleUrl = $categoryUrl . '/' . $aid . ".html";
    return $articleUrl;

}

// tree
function GetTree($data, $pId=0){
    $tree = '';
    foreach($data as $k => $v)
    {
        if($v['pid'] == $pId)
        {
            //父亲找到儿子
            $v['pid'] = getTree($data, $v['id']);
            $tree[] = $v;
            //unset($data[$k]);
        }
    }
    return $tree;
}
// 中文转json 乱码问题
function encode_json_url_encode($str) {
    return urldecode(json_encode(url_encode($str)));
}

function url_encode($str) {
    if(is_array($str)) {
        foreach($str as $key=>$value) {
            $str[urlencode($key)] = url_encode($value);
        }
    } else {
        $str = urlencode($str);
    }

    return $str;
}
// alert输出结果并跳转
function alertUrl($str, $url = '/', $EM = true) {
    echo "<script type='text/javascript' charset='UTF-8'>";
    if($EM){
        echo "window.parent.location.href = '".$url."';";
    }
    echo $str?"alert('".$str."');":"";
    echo "</script>";
    exit();
}
// alert输出结果并跳转
function alertJs($str, $EM = true) {
    echo "<script type='text/javascript' charset='UTF-8'>";
    if($EM){
        echo "window.history.back();";
    }
    echo $str?"alert('".$str."');":"";
    echo "</script>";
    exit();
}
/**
 * 获取当前时间
 * @static
 * @access public
 * @param string $str 时间格式
 * @return date/时间戳 $str 如果定义返回date 否 返回时间戳
 */
function GetNowTime($str = "Y-m-d H:i:s"){
    if(GetIsSet($str)){
        return date($str, time());
    }else{
        return time();
    }
}
/*
 *  判断数据是否定义
 *  @access putblic
 *  @param $StrData 参数 可以为任何类型的数据
 */
function GetIsSet($StrData){
    if(isset($StrData)){
        if($StrData === ''){
            return false;
        }else{
            return true;
        }
    }else{
        return false;
    }
}
function SetToNowtime($StrData){
    $time1=strtotime($StrData);
    $time2= GetNowTime('');
    $result= time_diff($time1,$time2);
    if($result['hours']>23){
        return $StrData;
    }else{
        if($result['hours']){
            return $result['hours'].'小时前';
        }else{
            if($result['minutes']){
                return $result['minutes'].'分钟前';
            }else{
                return $result['seconds'].'秒前';
            }
        }
    }
 }

/**
 * 计算时间差
 * @param int $timestamp1 时间戳开始
 * @param int $timestamp2 时间戳结束
 * @return array
 */
function time_diff($timestamp1, $timestamp2)
{
    if ($timestamp2 <= $timestamp1)
    {
        return array('hours'=> 0, 'minutes'=> 0, 'seconds'=> 0);
    }
    $timediff = $timestamp2 - $timestamp1;
    // 时
    $remain = $timediff%86400;
    $hours = intval($remain/3600);

    // 分
    $remain = $timediff%3600;
    $mins = intval($remain/60);
    // 秒
    $secs = $remain%60;

    $time = array('hours'=>$hours, 'minutes'=>$mins, 'seconds'=>$secs);

    return $time;
}
//邮箱注册
function smsmail($toaddr,$FromName,$Subject,$Body){

    Vendor('phpmailer.PHPMailerAutoload','','.php');

    //   set_time_limit(0);//设置脚本执行超时时间
    $mail = new PHPMailer;
    //https://
    //邮件服务器信息配置
    $mail -> ISSMTP();  //设置邮件发送协议 smtp|pop3|imap
    $mail -> CharSet = "utf-8";  //设置邮件编码
    $mail -> SMTPSecure = "ssl";  // SMTP 安全协议
    $mail -> Port = 465;          // 邮件端口
    $mail -> Host = "smtp.163.com"; // 使用的邮件服务器

    $mail -> SMTPAuth = true;  //设置phpmail发送邮件是否需要验(username&password)
    if($mail -> SMTPAuth){
        $mail -> Username = "18106172122";
        $mail -> Password = 'zxc123';//邮箱的授权密码
    }
    $mail -> From = "18106172122@163.com";    //来源from
    $mail -> IsHTML(true); // 是否发送html邮件

    //$mail->AddAttachment('./email.txt','email.txt'); // 添加附件,并指定名称

    //发送邮件信息
    $mail -> Addaddress($toaddr); // 收件人
    $mail-> FromName = $FromName;//发送人姓名
    $mail -> Subject = $Subject;   // 标题
    $mail -> Body = $Body;    // 内容

    if($mail->send()){
        echo '发送成功';
    }else{
        echo $mail->ErrorInfo;
    }
}

/*短信验证*/
function telcode($tel,$key){
    //企业ID $userid
    $userid = '51368';
//用户账号 $account
    $account = 'qianyuandatong';
//用户密码 $password
    $password = 'qianyuandatong123';
//发送到的目标手机号码 $mobile
    $mobile = $tel;

    $key = $key;

//短信内容 $content
    $content = "您的验证码是：".$key."。请不要把验证码泄露给其他人。【乾圆大通】";

//发送短信（其他方法相同）
    $gateway = "http://114.113.154.5/sms.aspx?action=send&userid={$userid}&account={$account}&password={$password}&mobile={$mobile}&content={$content}&sendTime=";
    $result = file_get_contents($gateway);
    $xml = simplexml_load_string($result);
    return (array('returnstatus'=>$xml->returnstatus,'message'=>$xml->message,'remainpoint'=>$xml->remainpoint,'taskID'=>$xml->taskID,'successCounts'=>$xml->successCounts));

}

//验证
function check($Datastring,$Datatime,$Datatype){
    if($Datastring){

    }else{
        SetFromCode();
    }
}
//设置
function SetFromCode($Datastring,$Datatime,$Datatype){
    Switch($Datatype){
        case 'tel';
            return;
            break;
        case 'email';
            return;
            break;
    }
}
//邮件验证 datatime 保存多长时间
function GetEmailCode($Datastring,$Datatime){

}
//短信验证 datatime 保存多长时间
function GetTelCode($Datastring,$Datatime){

}
//商品默认图片
function default_images(){
    $images=M('goods_image');
    $goods_image=$images->where('goods_id='.'0')->find();
    return $goods_image['image_url'];
}
function price($cart){
     $price=0;
     foreach ($cart as $key=>$value){
         $amount= $cart[$key]['amount'];
         $price += $amount;
     }
     return $price;
}








