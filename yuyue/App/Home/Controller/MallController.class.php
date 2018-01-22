<?php
/**
 *
 * 作    者：曹宇
 * 日    期：2017-10-5
 * 版    本：1.0.0
 * 功能说明：供应。
 *
 **/
namespace Home\Controller;
use Common\Controller\HomeController;

use Think\Controller;
class MallController extends HomeController
{
    protected $Mall_model;
    protected $Redeem_code_model;
    public function _initialize(){
        parent::_initialize();
        $this->Mall_model = M("mall");
        $this->Redeem_code_model = M("redeem_code");

    }
    public function index()
    {

        $where['id']=I('get.id');
        $data=$this->Mall_model->where($where)->find();
        $data['pic']=explode('|',$data['pic']);
        $this->assign('mall',$data);

        $recommend =$this->Mall_model->query('SELECT * FROM yf_mall  AS t1 JOIN (SELECT ROUND(RAND() * (SELECT MAX(id) FROM yf_mall)) AS id) AS t2 WHERE t1.id >= t2.id
ORDER BY t1.id ASC LIMIT 5;');
        foreach ($recommend as $key =>$value){
            $recommend[$key]['pic']=explode('|',$recommend[$key]['pic']);
            $recommend[$key]['thumb']= $recommend[$key]['pic'][0];
        }
        $this->assign('recommend',$recommend);
        $this->display();
    }

     public function yuyue()
     {
         $user = $this->users;
          if (IS_AJAX) {
             $mall_id = I('post.mall_id');

             if ($user['full_name'] && $user['id_code']) {
             }else{
                 $this->ajaxReturn(array('err' => -1, 'msg' => '请先实名认证，谢谢合作'));
             }
             //商品id
             $where['mid']=$mall_id;
             $mall=$this->Mall_model->where('id='.$mall_id)->find();
             if($mall['number']== 0){
                 $this->ajaxReturn(array('err'=>0,'msg'=>'商品已预约完，抱歉!'));
             }
             $where['uid']=$user['id'];
             //预约码表
             $mall_code=$this->Redeem_code_model->where($where)->find();
             if($mall_code){
                 $this->ajaxReturn(array('err'=>0,'msg'=>'您当前商品的兑换码未使用，请先使用，谢谢合作','code'=>$mall_code['code']));
             }
             if($mall_code['is_use']=='0'){
                 $this->ajaxReturn(array('err'=>0,'msg'=>'您当前商品的兑换码未使用，请先使用，谢谢合作','code'=>$mall_code['code']));
             }
             $data['uid']=$user['id'];
             $data['mid']=$mall_id;
             $data['full_name']=$user['full_name'];
             $data['id_code']=$user['id_code'];

             $data['update_time'] = GetNowTime();
             //有效时间
             $data['dlength'] = C('DLENGTH');
             $data['code']=random(12);
              $this->Mall_model->execute('UPDATE __TABLE__  SET number = number-1 WHERE id ='.$mall_id);
              $result=$this->Redeem_code_model->data($data)->add();
             if($result){
                 $this->ajaxReturn(array('err'=>1,'code'=>$data['code'],'msg'=>'预约成功，这是您的预约码'));
             }
         }
     }

    //php生成随机数据（数字、大小写字母混合）
    function random($length, $chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ') {
        $hash = '';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;


    }
}