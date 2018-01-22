<?php
namespace User\Controller;

class ServiceController extends ComController
{
    protected $Redeem_code_model;
    public function _initialize(){
        parent::_initialize();
        $this->Redeem_code_model = M("redeem_code");

    }
    public function Index()
    {
        $user=$this->users;
        $code=$this->Redeem_code_model
            //设置别名 b mall  c users
            ->alias("a")
            //你所需要的字段
            ->field('a.*,c.title' )
            ->join("__USERS__ b ON a.uid = b.id")
            ->join("__MALL__ c ON a.mid = c.id")
            ->order('is_use desc')
            ->select();
        $p =intval( I('get.p'))? intval(I('get.p')):1;
        $pagesize = 5;//每页显示多少个
        $count=$this->Redeem_code_model
            //设置别名 b mall  c users
            ->alias("a")
            //你所需要的字段
            ->field('a.*,c.title' )
            ->join("__USERS__ b ON a.uid = b.id")
            ->join("__MALL__ c ON a.mid = c.id")
            ->where('a.uid='.$user['id'])
            ->order('is_use desc')
            ->count();

        //计算一共有多少个
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $redeem_code=$this->Redeem_code_model
            //设置别名 b mall  c users
            ->alias("a")
            //你所需要的字段
            ->field('a.*,c.title,c.pic' )
            ->join("__USERS__ b ON a.uid = b.id")
            ->join("__MALL__ c ON a.mid = c.id")
            ->where('a.uid='.$user['id'])
            ->limit($offset . ',' . $pagesize)
            ->order('is_use desc')
            ->select();
        foreach ($redeem_code as $key =>$value){
            $redeem_code[$key]['pic'] =explode('|',$redeem_code[$key]['pic']);
            $redeem_code[$key]['thumb']=$redeem_code[$key]['pic'][0];
        }
        $page = new \Think\Page($count, $pagesize);//实例化
        $page = $page->show();//输出模板
        $this->assign('page', $page);
        $this->assign('redeem_code', $redeem_code);

        $this->display();
    }

}