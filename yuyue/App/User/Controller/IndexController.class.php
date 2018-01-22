<?php
namespace User\Controller;

class IndexController extends ComController
{
    protected $Users_model;

    public function _initialize(){
         parent::_initialize();
        $this->Users_model = M("users");
     }
    public function index(){

        $this->display();
    }
}