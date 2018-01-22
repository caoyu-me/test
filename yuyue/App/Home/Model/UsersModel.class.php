<?php
/**
 * Created by PhpStorm.
 * User: Caoyu
 * Date: 2017/9/30
 * Time: 9:24
 */
namespace Home\Model;
use Think\Model;
class UsersModel extends Model
{
    //自动完成
    protected $_auto = array (
        array('pwd','password',1,'function'),
    );
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('name','','用户名已存在！',0,'unique',3),
        array('tel','','手机号已存在！',0,'unique',3),
        array('email','email','邮箱已存在!',0,'unique',3),
        array('pwd','/^\w{6,12}$/','密码必须是6-12位的数字、字母、下划线',1,'regex',1),
//        array('repass','pwd','确认密码不正确',1,'confirm',1), // 验证确认密码是否和密码一致
    );

}