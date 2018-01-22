<?php
/**
 *
 * 版权所有：恰维网络<qwadmin.qiawei.com>
 * 作    者：小马哥<hanchuan@qiawei.com>
 * 日    期：2015-09-17
 * 版    本：1.0.3
 * 功能说明：文件上传控制器。
 *
 **/

namespace User\Controller;

use Think\Upload;

class UploadController extends ComController
{
    protected $Users_model;

    public function _initialize()
    {
        parent::_initialize();

        $this->Users_model = M("users");

    }
    public function index($type = null)
    {

    }

    public function uploadpic()
    {

        $Img = I('Img');
        //图片的种类
        $kind=I('get.kind');
        $id=I('get.id');
        $Path = null;

        if ($_FILES['img']) {
            $Img = $this->saveimg($_FILES,$kind);

            $this->save($Img,$kind,$id);
        }

        $BackCall = I('BackCall');
        $Width = I('Width');
        $Height = I('Height');
        if (!$BackCall) {
            $Width = $_POST['BackCall'];
        }
        if (!$Width) {
            $Width = $_POST['Width'];
        }
        if (!$Height) {
            $Width = $_POST['Height'];
        }
        $this->assign('Width', $Width);
        $this->assign('BackCall', $BackCall);
        $this->assign('Img', $Img);
         $this->assign('Height', $Height);
        $this->display('Uploadpic');
    }
    //图片上传处理
    private function saveimg($files,$kind)
    {
        $mimes = array(
            'image/jpeg',
            'image/jpg',
            'image/jpeg',
            'image/png',
            'image/pjpeg',
            'image/gif',
            'image/bmp',
            'image/x-png'
        );
        $exts = array(
            'jpeg',
            'jpg',
            'jpeg',
            'png',
            'pjpeg',
            'gif',
            'bmp',
            'x-png'
        );
        //获取上传的图片的参数 0:宽 1:高
        $imagesize=getimagesize($files['img']['tmp_name']);
        switch ($kind){
            //用户头像
            case 'user';
                if($imagesize[0]>200 or $imagesize[1]>200){
                    alertJs('您的图片太大了，建议200*200',U('/user/user'));
                }
                $savepath ='/Uploads/User/'.date('Y')."/".date('m')."/";
            break;
            //商品
          /*  case 'sell';
                if($imagesize[0]>400 or $imagesize[1]>300){
                    alertJs('您的图片太大了，建议400*300');
                }
                $savepath ='Uploads/Sell/'.date('Y')."/".date('m')."/";
                break;
            break;
            case 'buy';
                if($imagesize[0]>400 or $imagesize[1]>300){
                    alertJs('您的图片太大了，建议400*300');
                }
                $savepath ='Uploads/Buy/'.date('Y')."/".date('m')."/";
                break;
                break;*/
        }

        $upload = new Upload(array(
            'mimes' => $mimes,
            'exts' => $exts,
            'rootPath' => './Public',
            'savePath' => $savepath,// 设置附件上传目录    // 上传文件
            'subName'  =>  array('date', 'd'),// 设置别名    // 上传文件
        ));

        $info = $upload->upload($files);
        if(!$info) {// 上传错误提示错误信息
            $error = $upload->getError();
            echo "<script>alert('{$error}')</script>";
        }else{// 上传成功
            foreach ($info as $item) {
                $filePath[] = __ROOT__."/Public".$item['savepath'].$item['savename'];
            }
            $ImgStr = implode("|", $filePath);
            return $ImgStr;
        }
    }

    private function save($Img,$kind,$id){
        $user=$this->users;
        $where['id']=$id;
          switch ($kind){
            case 'user';
                //上传后的图片地址
                $data['avatar'] = $Img;
                 if ($user['avatar']) {
                     if (!unlink('.' . $user['avatar'])) {
                        // echo('删除失败');
                    } else {
                        //echo('删除成功');
                    }
                    $result = $this->Users_model->where($where)->data($data)->save();
                     alertUrl('头像修改成功！',U('/user'));
                 }else{
                    $result = $this->Users_model->where($where)->data($data)->save();
                 }

                break;
           /* case 'sell';
                $model=$this->Metal_sell_model;
                $this->imgget($model,$id,$Img);
                break;
            case 'buy';
                $model=$this->Metal_buy_model;
                $this->imgget($model,$id,$Img);
                break;*/
        }
    }


    //分类处理
    public function batchpic()
    {
        $ImgStr = I('Img');
        $ImgStr = trim($ImgStr, '|');
        $Img = array();
        if (strlen($ImgStr) > 1) {
            $Img = explode('|', $ImgStr);
        }
        $Path = null;
        $newImg = array();
        $newImgStr = null;
        if ($_FILES) {
            $newImgStr = $this->saveimg($_FILES);
            if ($newImgStr) {
                $newImg = explode('|', $newImgStr);
            }

        }
        $Img = array_merge($Img,$newImg);
        $ImgStr = implode("|", $Img);
        $BackCall = I('BackCall');
        $Width = I('u');
        $Height = I('Height');
        if (!$BackCall) {
            $Width = $_POST['BackCall'];
        }
        if (!$Width) {
            $Width = $_POST['Width'];
        }
        if (!$Height) {
            $Width = $_POST['Height'];
        }
        $this->assign('Width', $Width);
        $this->assign('BackCall', $BackCall);
        $this->assign('ImgStr', $ImgStr);
        $this->assign('Img', $Img);
        $this->assign('Height', $Height);
        $this->display('Batchpic');
    }

    //编辑图片
    private function imgget($model,$id,$Img)
    {

        //修改时
        $where['id']=$id;
        //上传后的图片地址
        $data['pic'] = $Img;
        $data['update_time']=GetNowTime();
        if($where['id']){
            $buyimg=$model->where($where)->find();
            if ($buyimg['pic']) {
                if (!unlink('.' . $buyimg['pic'])) {
                    // echo('删除失败');
                } else {
                    //echo('删除成功');
                }
            }
            $result = $model->data($data)->where($where)->save();
        }else{
            $newbuy = $model->order('update_time desc')->find();
            if($newbuy['uid']){
                //添加一条新的数据
                $model->data($data)->add();
            }else{
                //修改这一条数据
                if (!unlink('.' . $newbuy['pic'])) {
                    // echo('删除失败');
                } else {
                    //echo('删除成功');
                }
                $model->data($data)->where(array('id'=>$newbuy['id']))->save();
            }
        }
    }
}
