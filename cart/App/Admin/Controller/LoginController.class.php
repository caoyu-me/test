<?php
/**
 *
 * 版权所有：恰维网络<qwadmin.qiawei.com>
 * 作    者：寒川<hanchuan@qiawei.com>
 * 日    期：2016-01-17
 * 版    本：1.0.0
 * 功能说明：后台登录控制器。
 *
 **/

namespace Admin\Controller;

class LoginController extends ComController
{
    protected $Admin_model;

    public function _initialize() {
        parent::_initialize();
        $this->Admin_model = M("admin");
    }

    public function index()
    {
        $flag = $this->check_login();
        if ($flag) {
            $this->error('您已经登录,正在跳转到主页', U("index/index"));
        }

        $this->display();
    }

    public function login()
    {
        $where['name'] = I('post.name') ? trim(I('post.name')) : '';
        $password      = I('post.password') ? password(trim(I('post.password'))) : '';
        $remember      = I('post.remember') ? I('post.remember') : 0;

        if ($where['name'] == '') {
            $this->error('用户名不能为空！', U("login/index"));
        } elseif ($password == '') {
            $this->error('密码必须！', U("login/index"));
        }

        $admin = $this->Admin_model->where($where)->find();
        // var_dump($this->Admin_model->getLastSql());
        $verify = I('param.verify','');

        if (!$this->check_verify($verify,'login')) {
            alertJs("验证码错误");
        }


        if ($admin) {
            if($password != $admin['password']){
                addlog('登录失败。', $where['name']);
                $this->error('登录失败，请重试！', U("login/index"));
            }

            session('admin_id',$admin['id']);
            addlog('登录成功。', $where['name']);
            $url = U('index/index');
            header("Location: $url");
        } else {
            addlog('登录失败。', $where['name']);
            $this->error('登录失败，请重试！', U("login/index"));
        }
    }

    function check_verify($code, $id = '')
    {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    public function verify()
    {
        $config = array(
            'fontSize' => 14, // 验证码字体大小
            'length' => 4, // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
            'imageW' => 100,
            'imageH' => 30,
        );
        $verify = new \Think\Verify($config);
        $verify->entry('login');
    }
}
