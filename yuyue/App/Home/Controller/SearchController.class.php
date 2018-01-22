<?php
/**
 * Created by PhpStorm.
 * User: caoyu-me
 * Date: 2017/11/21
 * Time: 10:24
 * 功能说明：注册
 */
namespace Home\Controller;
use Common\Controller\HomeController;

use Think\Controller;
class SearchController extends HomeController
{
    protected $Article_model;
    public function _initialize(){
        parent::_initialize();
        $this->Article_model = M("article");
    }
    public function Index()
    {
        $title=trim(I('post.title'));
        if($title==""){
            alertJs('请输入您要查找的内容！');
        }
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $pagesize = 10;//每页显示多少个
        $where['a.title']=array('like',"%$title%");
        $where['a.status']=1;

        $count= $this->Article_model
            ->alias("a")
            ->join("__MNAV__ b ON a.nav = b.id")
            ->field('a.*,b.url')->where($where)->count();//计算一共有多少个

        $offset = $pagesize * ($p - 1);//计算记录偏移量

        $con= $this->Article_model
            ->alias("a")
            ->join("__MNAV__ b ON a.nav = b.id")
            ->field('a.*,b.url')
            ->where($where)
            ->limit($offset . ',' . $pagesize)->select();

        $page = new \Think\Page($count, $pagesize);//实例化
        $page = $page->show();//输出模板

        //最新的咨讯
        $new_article=$this->Article_model->where(array('nav'=>7))->order('update_time desc')->select();
        //点击排行
        $click_article=$this->Article_model->where(array('nav'=>7))->order('click desc')->select();
        $this->assign('page', $page);
        $this->assign('new_article', $new_article);
        $this->assign('click_article', $click_article);
        $this->assign('con', $con);
        $this->display();
    }

}