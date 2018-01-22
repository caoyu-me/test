<?php
/**
 * Created by PhpStorm.
 * User: caoyu-me
 * Date: 2017/11/14
 * Time: 20:28
 */
namespace Home\Controller;
use Common\Controller\HomeController;

use Think\Controller;
class SellController extends HomeController{
    protected $Food_model;
    public function _initialize(){
        parent::_initialize();

        $this->Food_model = M("food");

    }
    public  function Index()
    {
        $food =$this->Food_model->select();
        $this->assign('food',$food);
        $this->display();
    }


}