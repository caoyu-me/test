<?php
namespace Home\Controller;
use Common\Controller\HomeController;

use Think\Controller;
class NewsController extends HomeController
{

    protected $Article_model;
    protected $Review_model;
    public function _initialize(){
        parent::_initialize();
        $this->Article_model = M("article");
        $this->Review_model = M("review");
    }
    public function Index()
    {

        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $pagesize = 10;//每页显示多少个
        $count= $this->Article_model->where(array('nav'=>9))->count();//计算一共有多少个
        $offset = $pagesize * ($p - 1);//计算记录偏移量

        $article= $this->Article_model
            ->where(array('nav'=>9))
            ->limit($offset . ',' . $pagesize)->select();
        $page = new \Think\Page($count, $pagesize);//实例化
        $page = $page->show();//输出模板

        $this->getarist();
        $this->assign('page', $page);
        $this->assign('article', $article);
        $this->display();
    }
    public function detail()
    {
        $id=I('get.id');
        $article=$this->Article_model->where(array('id'=>$id))->find();
        $data['click']=$article['click'];
        $data['click']++;
        $this->Article_model->where(array('id'=>$id))->save($data);

        //获取当前文章评论数
        $count=$this->Review_model
                    ->alias("a")
                    ->join("__USERS__ b ON a.uid=b.id")
                    ->field('a.*,b.avatar,b.name')
                    ->where(array('aid'=>$id))->count();

        $totalPages = ceil($count /C('plnum')); //总页数
        $this->getarist();
        $this->assign('totalPages',$totalPages);
        $this->assign('article',$article);
        $this->assign('count',$count);
        $this->display();
    }
    private function getarist(){
        if(S('new_article')){
            $new_article=S('new_article');
        }else{
            //最新的咨讯
            $new_article=$this->Article_model->where(array('nav'=>9))->order('update_time desc')->select();
            S('new_article',$new_article,60*60*24);
        }
        if(S('click_article')){
            $click_article=S('click_article');
        }else{
            //点击排行
            $click_article=$this->Article_model->where(array('nav'=>9))->order('click desc')->select();
            S('click_article',$click_article,60*60*24);
        }
        $this->assign('new_article', $new_article);
        $this->assign('click_article', $click_article);
    }
}