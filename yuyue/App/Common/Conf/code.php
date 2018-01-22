<?php
/**
 *
 * 版权所有：恰维网络<www.qiawei.com>
 * 作    者：曹宇
 * 日    期：2016-10-06
 * 版    本：1.2.0
 * 功能说明：配置文件。
 *
 **/
return array(
    'EMAIL_CHARSET'    => 'utf-8', // 设置邮件编码
    'EMAIL_SMTPSECURE'    => 'ssl', // SMTP 安全协议
    'EMAIL_PROT'    => '465', // 邮件端口
    'EMAIL_HOST'    => 'smtp.qq.com', // 使用的邮件服务器
    'EMAIL_NAME'     => '1481619987', // 邮箱name
    'EMAIL_PWD'    => 'gmcrqjuvfsyygccj', // 邮箱的授权密码
    'EMAIL_FROM'  => '1481619987@qq.com', // 邮箱地址
    'EMAIL_SUBJECT'=>array('账号激活','验证码'),
    'EMAIL_REGBODY'=>array('http://zx.qydtme.com/register/verification?email=#&code=@','您的验证码是：@。请不要把验证码泄露给其他人。【乾圆大通】'),
    //短信
    'TEL_USERID' =>  '51368',      // 企业ID
    'TEL_ACCOUNT' =>  'qianyuandatong',      // 用户账号
    'TEL_PASSWORD' =>  'qianyuandatong123',      // 用户密码
    'TEL_REGCONTENT' =>  '您的验证码是：@。请不要把验证码泄露给其他人。【乾圆大通】',      //短信内容 模板
);