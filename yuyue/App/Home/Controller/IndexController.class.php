<?php
namespace Home\Controller;
use Common\Controller\HomeController;

class IndexController extends HomeController {
    protected $Banner_model;
    protected $Mall_model;

    public function _initialize(){
        parent::_initialize();
        $this->Banner_model = M("banner");
        $this->Mall_model = M("mall");
    }
    public function index(){

     /*   当前控制器的名字 = 某值  调用  对应数组下的值*/

        //商品
        $mall=$this->Mall_model->order('update_time desc')->limit('10')->select();
        foreach ($mall as $key =>$value){
            $mall[$key]['pic']=explode('|',$mall[$key]['pic']);
           $mall[$key]['thumb']= $mall[$key]['pic'][0];
        }
        $this->assign('mall',$mall);


        //轮播
        $banner=$this->Banner_model->where(array('status'=>1))->select();
        $this->assign('banner',$banner);

        $this->display();
    }
    public function ajax($p)
    {
        $pagesize = 10;//每页显示多少个
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        $where['status']=1;
        $result= $this->Mall_model //设置别名
            ->where($where)
            ->order('update_time desc')
            ->limit($offset . ',' . $pagesize)
            ->select();
        foreach ($result as $key =>$value){
            $result[$key]['pic']=explode('|',$result[$key]['pic']);
            $result[$key]['thumb']= $result[$key]['pic'][0];
        }
        if($result){
            $this->ajaxReturn(array('err'=>1,'mess'=>$result),'json');
        }else{
            $this->ajaxReturn(array('err'=>0,'mess'=>''),'json');
        }
    }

}