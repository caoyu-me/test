<?php
/**
 * Created by PhpStorm.
 * User: Caoyu
 * Date: 2017/9/30
 * Time: 9:24
 */
namespace User\Model;
use Think\Model;
class UsersModel extends Model
{

    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)

        array('name','','用户名已存在！',0,'unique',2),
        array('full_name','require','真实姓名不能为空！'),
        array('tel','','手机号已存在！',0,'unique',2),
        array('email','','邮箱已存在！',0,'unique',2),
        array('id_code','','身份证号已存在！',0,'unique',2),
    );

}