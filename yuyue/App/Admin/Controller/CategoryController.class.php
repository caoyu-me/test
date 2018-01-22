<?php
/**
 *
 * 版权所有：恰维网络<qwadmin.qiawei.com>
 * 作    者：寒川<hanchuan@qiawei.com>
 * 日    期：2016-09-20
 * 版    本：1.0.0
 * 功能说明：文章控制器。
 *
 **/

namespace Admin\Controller;

use Tree\Tree;

class CategoryController extends ComController
{

    public function index()
    {
        $category = M('kinds')->field('id,pid,title')->select();
        $category = $this->getMenu($category);
        $this->assign('category', $category);//导航
        $this->display();
    }

    public function del()
    {

        $id = isset($_GET['id']) ? intval($_GET['id']) : false;
        if ($id) {
            $data['id'] = $id;
            $category = M('kinds');
            if ($category->where('pid=' . $id)->count()) {
                die('2');//存在子类，严禁删除。
            } else {
                $category->where('id=' . $id)->delete();
                addlog('删除分类，ID：' . $id);
            }
            die('1');
        } else {
            die('0');
        }

    }

    public function edit()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : false;
        $category = M('kinds')->field('id,pid,title')->where('id=' . $id)->select();
        $this->assign('category', $category[0]);//子类

        $this->display('form');
    }

    public function add()
    {
        $pid = I('get.pid');

        if($pid == null){
            $category_menu = M('kinds')->field('id,pid,title')->select();
            $category_menu = $this->getMenu($category_menu);
        }else{
            $category = M('kinds')->field('id,pid,title')->where('id='.$pid)->select();
            $category_menu =$category;
        }
        //所有的分类
        //当前的分类 $category


        $this->assign('category_menu', $category_menu);//导航

        $this->assign('category', $category[0]);//导航


        $this->display('add');
    }

    public function update($act = null)
    {
        if ($act == 'order') {
            $id = I('post.id', 0, 'intval');
            if (!$id) {
                die('0');
            }
            $o = I('post.o', 0, 'intval');
            M('kinds')->data(array('o' => $o))->where("id='{$id}'")->save();
            addlog('分类修改排序，ID：' . $id);
            die('1');
        }

        //id
        $id = I('post.id', false, 'intval');
        //mingzi
        $data['title'] = I('post.name');
        //排序
        $data['o'] = I('post.o', 0, 'intval');

        if ($data['title'] == '') {
            alertJs('分类名称不能为空！');
        }

        if ($id) {
            if (M('kinds')->data($data)->where('id=' . $id)->save()) {
                addlog('分类修改，ID：' . $id . '，名称：' . $data['name']);
                alertUrl('恭喜，分类修改成功！',U('category/index'));
                die(0);
            }
        } else {
            //类别
            $data['pid'] = I('post.pid', 0, 'intval');
            $id = M('kinds')->data($data)->add();
            if ($id) {
                addlog('新增分类，ID：' . $id . '，名称：' . $data['name']);
                alertUrl('恭喜，新增分类成功！', U('category/index'));
                die(0);
            }
        }
        alertUrl('恭喜，操作成功！',U('category/index'));
    }
}
