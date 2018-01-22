<?php
namespace Home\Controller;
use Common\Controller\HomeController;

class IndexController extends HomeController {

    protected $Food_model;
    protected $Store_model;
    public function _initialize(){
        parent::_initialize();

        $this->Food_model = M("food");
        $this->Store_model = M("store");

    }
    public  function Index()
    {
        $food =$this->Food_model
                    ->alias("a")
                    ->join('__STORE__ c on a.store = c.store_id')
                    ->field('a.*,c.store_name')->select();

        $this->assign('food',$food);
        $this->display();
    }

}