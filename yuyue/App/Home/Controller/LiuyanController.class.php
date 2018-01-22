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
class LiuyanController extends HomeController
{
    protected $Message_model;
    protected $Metal_sell_model;
    protected $Metal_buy_model;
    protected $Article_model;
    protected $Review_model;

     public function _initialize(){
        parent::_initialize();
        $this->Message_model = M("message");
        $this->Metal_buy_model = M("metal_buy");
        $this->Metal_sell_model = M("metal_sell");
        $this->Article_model = M("article");
        $this->Review_model = M("review");
     }
     //供应 求购 用户留言
    public function index()
    {
         $user=$this->users;
         $data['title']=I('post.title');
         $data['body']=I('post.body');
         $article_id=I('post.article_id');

         //0 买  1卖
         if(I('post.act')==0){
             $tab='Buy';
             $article=$this->Metal_buy_model
                            ->alias("a")
                            ->join("__USERS__ b ON a.uid=b.id")
                            ->where(array('a.id'=>$article_id))
                            ->field('b.id,b.name,b.status as user_status')
                            ->find();

         }else if(I('post.act')==1){
             $tab='Sell';
             $article=$this->Metal_sell_model
                 ->alias("a")
                 ->join("__USERS__ b ON a.uid=b.id")
                 ->where(array('a.id'=>$article_id))
                 ->field('b.id,b.status as user_status')
                 ->find();
         }
        $url= U('/'.$tab.'/detail', array('id'=>$article_id));

         //人物的状态
        if($article['user_status'] == 1){
             if(C('lystatus')){
                 $data['attest']=1;
             }else{
                 $data['attest']=0;
             }
            $wwhere['uid']=$user['id'];
            $wwhere['kind']=$article['id'];
            $message=$this->Message_model->where($wwhere)->order('update_time desc')->find();

            if($message){
               $timer=time_diff($message['update_time'],GetNowTime());

               if($timer['hours']<1){
                   alertUrl('请等待1小时后发送',$url);
               }
            }
        }else{
            alertUrl('发送失败',$url);
        }

        if($article['id']== $user['id']){
            alertUrl('不能给自己发送',$url);
        }

        //接收方
        if($article){
            $data['kind']=$article['id'];
        }

         if($data['title'] && $data['body']){
             //发送方
             $data['uid']=$user['id'];
             //更新时间
             $data['update_time']=GetNowTime();
         }

         $result=$this->Message_model->data($data)->add();
         if($result){
             alertUrl('发送成功',$url);
         }else{
             alertUrl('发送失败',$url);
         }

    }
    //新闻评论 增加
    public function ajaxdate()
    {
        $user=$this->users;
//        dump($user);
        if($user){
            //文章id
            $where['id']=intval(I('post.aid'));
            //模型  1:文章
            $kind=I('post.kind');
            if($where['id'] && $kind && $user){
                $err=1;
                switch ($kind){
                    case 1:
                        $model=$this->Article_model;
                        break;

                    default:
                        $err=0;
                        break;
                }

                if($err){
                    $num=$model->where($where)->count();
                    if($num){
                        //评论人id
                        $rwhere['uid']=$user['id'];
                        //文章id
                        $rwhere['aid']=$where['id'];
                        $review=$this->Review_model->where($rwhere)->order('update_time desc')->find();
//                        dump($review);
                        if($review){
                            $timer=time_diff($review['update_time'],GetNowTime());
//                            var_dump($timer['hours']<1);
                           if($timer['hours']<1){
                                $err=4;
                            }
                        }
                        if($err == 1){
                            $data['uid']= $user['id'];
                            $data['pid']= 0;
                            $data['aid']= intval(I('post.aid'));
                            $data['kind']= intval(I('post.kind'));
                            $data['body']= I('post.body');

                            $data['update_time']= GetNowTime();
                            $data['status']= C('plstatus')?0:1;

                            $result = $this->Review_model ->data($data)->add();

                            if($result){
                                if(C('plstatus')){
                                    $err=3;
                                }else{
                                    $err=1;
                                }
                            }else{
                                $err=0;
                            }
                        }

                    }else{

                        $err=0;
                    }
                }

            }else{

                $err=0;
            }
        }else{
            echo(123);
            $err=2;
        }

        echo encode_json_url_encode(array('err'=>$err));
    }
    //新闻评论 查询
    public function ajaxlist()
    {
        //文章id
        $where['a.aid']=intval(I('get.aid'));
        //模型  1:文章
        $where['a.kind']=I('get.kind');
        $where['a.status']=1;
        if($where['a.aid'] && $where['a.kind']){
            $err=1;
            $p= intval(I('get.p'))?intval(I('get.p')) :1;
            $pagesize = C('plnum');//每页显示多少个
            $offset = $pagesize * ($p - 1);//计算记录偏移量

            $datalist= $this->Review_model
                ->alias("a")
                ->join("__USERS__ b ON a.uid=b.id")
                ->field('a.*,b.avatar,b.name')
                ->where($where)
                ->limit($offset . ',' . $pagesize)
                ->order('id desc')
                ->select();
//            dump($this->Review_model->getLastSql());
        }else{
            $err=0;
        }
        $this->ajaxReturn(array('err'=>$err,'mess'=>$datalist));

        echo json_encode(array('err'=>$err,'mess'=>$datalist));
        echo encode_json_url_encode(array('err'=>$err,'mess'=>$datalist));

    }



}