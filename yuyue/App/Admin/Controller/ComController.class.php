<?php
/**
 *
 * 版权所有：恰维网络<qwadmin.qiawei.com>
 * 作    者：寒川<hanchuan@qiawei.com>
 * 日    期：2015-09-17
 * 版    本：1.0.0
 * 功能说明：后台公用控制器。
 *
 **/

namespace Admin\Controller;

use Common\Controller\BaseController;
use Think\Auth;

class ComController extends BaseController
{
    public $ADMIN;

    public function _initialize()
    {
        // parent::_initialize();
        $this->Admin_model = M("admin");

        C(setting());
        if (!C("COOKIE_SALT")) {
            $this->error('请配置COOKIE_SALT信息');
        }
        /**
         * 不需要登录控制器
         */
        if (in_array(CONTROLLER_NAME, array("Login","Logout"))) {
            return true;
        }

        //检测是否登录
        $flag =  $this->check_login();
        $url = U("login/index");
        if (!$flag) {
            header("Location: {$url}");
            exit(0);
        }

        $m = M();
        $ADMIN = $this->ADMIN;
        $prefix = C('DB_PREFIX');
        $AID = $ADMIN['id'];
        $userinfo = $m->query("SELECT * FROM {$prefix}auth_group g left join {$prefix}auth_group_access a on g.id=a.group_id where a.uid=$AID");
        $this->assign('CLASSID', $userinfo[0]['id']);
        $Auth = new Auth();
        $allow_controller_name = array('Upload','Index');//放行控制器名称
        $allow_action_name = array();//放行函数名称
        if ($userinfo[0]['group_id'] != 1 && !$Auth->check(CONTROLLER_NAME . '/' . ACTION_NAME,
                $AID) && !in_array(CONTROLLER_NAME, $allow_controller_name) && !in_array(ACTION_NAME,
                $allow_action_name)
        ) {
            $this->error('没有权限访问本页面!');
        }

        // p($userinfo[0]);
        $ADMIN['rules']    = $userinfo[0]['rules'];
        $ADMIN['title']    = $userinfo[0]['title'];
        $ADMIN['group_id'] = $userinfo[0]['group_id'];
        $this->ADMIN       = $ADMIN;
        session('admin', $ADMIN);
        $this->assign('admin', $ADMIN);

        $current_action_name = ACTION_NAME/* == 'edit' ? "index" : ACTION_NAME*/;
        $current = $m->query("SELECT s.id,s.title,s.name,s.tips,s.pid,p.pid as ppid,p.title as ptitle FROM {$prefix}auth_rule s left join {$prefix}auth_rule p on p.id=s.pid where s.name='" . CONTROLLER_NAME . '/' . $current_action_name . "'");

        $this->assign('current', $current[0]);

        $menu_access_id = $userinfo[0]['rules'];
        if ($userinfo[0]['group_id'] != 1) {

            $menu_where = "AND id in ($menu_access_id)";

        } else {

            $menu_where = '';
        }
        $menu = M('auth_rule')->field('id,title,pid,name,icon')->where("islink=1 $menu_where ")->order('o ASC')->select();
        $menu = $this->getMenu($menu);
        $this->assign('menu', $menu);
    }


    protected function getMenu($items, $id = 'id', $pid = 'pid', $son = 'children')
    {
        $tree = array();
        $tmpMap = array();
        //修复父类设置islink=0，但是子类仍然显示的bug @感谢linshaoneng提供代码
        foreach( $items as $item ){
            if( $item['pid']==0 ){
                $father_ids[] = $item['id'];
            }
        }
        //----
        foreach ($items as $item) {
            $tmpMap[$item[$id]] = $item;
        }

        foreach ($items as $item) {
            //修复父类设置islink=0，但是子类仍然显示的bug by shaoneng @感谢linshaoneng提供代码
            if( $item['pid']<>0 && !in_array( $item['pid'], $father_ids )){
                continue;
            }
            //----
            if (isset($tmpMap[$item[$pid]])) {
                $tmpMap[$item[$pid]][$son][] = &$tmpMap[$item[$id]];
            } else {
                $tree[] = &$tmpMap[$item[$id]];
            }
        }
        return $tree;
    }

    public function check_login(){
        session_start();
        $flag = false;
        $id = session('admin_id');
        if ($id) {
            $admin = M('admin')->where(array('id' => $id))->find();

            if ($admin) {
                $flag = true;
                $this->ADMIN = $admin;
            }
        }
        return $flag;
    }
}