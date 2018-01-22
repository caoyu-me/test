<?php
/**
 *
 * 版权所有：恰维网络<qwadmin.qiawei.com>
 * 作    者：寒川<hanchuan@qiawei.com>
 * 日    期：2016-09-20
 * 版    本：1.0.0
 * 功能说明：商品控制器。
 *
 **/

namespace Admin\Controller;

use Think\Upload;
class GoodsController extends ComController
{
    protected $Mall_model;
    protected $Kinds_model;

    public function _initialize(){
        parent::_initialize();
        $this->Mall_model = M("mall");
        $this->Kinds_model = M("kinds");
    }
    public function index($sid = 0, $p = 1)
    {

        $p = intval($p) > 0 ? $p : 1;

        $article = M('mall');
        $pagesize = 20;#每页数量
        $offset = $pagesize * ($p - 1);//计算记录偏移量
        //表前缀
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $where = 'status = 1 ';
        //默认按照时间降序
        $orderby = "update_time desc";
        if ($order == "asc") {

            $orderby = "update_time asc";
        }
        //获取栏目分类
        $category = M('kinds')->field('id,pid,title')->select();
        $category = $this->getMenu($category);
        $this->assign('category', $category);//导航

        $count = $article->where($where)->count();

        $list = $article
            //设置别名
            ->alias("a")
            //你所需要的字段
            ->field('a.*,b.title as kinds_title')
            //链接表，设置条件
            ->join("__KINDS__ b ON a.kind = b.id")
            ->where($where)
            ->order($orderby)
            ->limit($offset . ',' . $pagesize)->select();

        $page = new \Think\Page($count, $pagesize);
        $page = $page->show();
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->display();
    }
    public function add()
    {
        $category = M('kinds')->field('id,pid,title')->select();
        $category = $this->getMenu($category);
        $this->assign('category', $category);//导航
        $this->display('form');
    }
    //处理添加
    public function update($aid = 0)
    {
        //1:admin
        $data['uid']=1;
        //是否修改
        $aid = intval($aid);
        //分类kind，标题title，库存number，价格price。详情body，图片pic，更新时间update_time，简介intro，点击次数click，
        $data['kind'] = isset($_POST['kind']) ? intval($_POST['kind']) :false;
        $data['title'] = isset($_POST['title']) ? $_POST['title'] : false;
        $data['number']=I('post.number','','strip_tags');
        $data['price']=I('post.price','','strip_tags');
        $data['body']=I('post.body');
        $data['intro']=I('post.intro','','strip_tags');
        $data['update_time'] = GetNowTime();
        $data['status'] =1;
        $data['pic']=I('post.pic');
        if (!$data['kind'] or !$data['title'] or !$data['body']) {
            alertJs('警告！商品分类、商品标题及商品内容为必填项目。');
        }
        //页面的图片
        //修改时
        $img='';
        foreach($data['pic'] as $key=>$value){

            $img=$img.$data['pic'][$key].'|';
        }
        $data['pic']=$img;

        //上传图片
        if($_FILES['pic']['name'][0]){
            $img_file= $this->saveimg($_FILES);
        }
        $data['pic']=$data['pic'].$img_file;

            //删除旧图片


        if ($aid) {
            $this->Mall_model->data($data)->where('id=' . $aid)->save();
            addlog('编辑商品，AID：' . $aid);
            alertUrl('恭喜！商品编辑成功！',U('goods/index'));
        } else {
            $aid = $this->Mall_model->data($data)->add();
            if ($aid) {
                addlog('新增商品，AID：' . $aid);
                alertUrl('恭喜！商品新增成功！',U('admin/goods'));
            } else {
                alertJs('抱歉，未知错误！',U('admin/goods'));
            }

        }
    }
    //删除
    //修改
    public function edit($aid)
    {

        $aid = intval($aid);
        $mall =$this->Mall_model->where('id=' . $aid)->find();
        if ($mall) {
            $category = M('kinds')->field('id,pid,title')->select();
            $category = $this->getMenu($category);
            $this->assign('category', $category);//导航
            $this->assign('mall', $mall);
        } else {
            $this->error('参数错误！');
        }
        $this->display('form');
    }
    public function del()
    {

        $aids = isset($_REQUEST['aids']) ? $_REQUEST['aids'] : false;
        if ($aids) {
            if (is_array($aids)) {
                $aids = implode(',', $aids);
                $map['aid'] = array('in', $aids);
            } else {
                $map = 'aid=' . $aids;
            }
            if (M('article')->where($map)->delete()) {
                addlog('删除文章，AID：' . $aids);
                $this->success('恭喜，文章删除成功！');
            } else {
                $this->error('参数错误！');
            }
        } else {
            $this->error('参数错误！');
        }
    }

        //上传图片
    private function saveimg($files)
    {
        //图片的规格
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
        foreach($files['pic']['tmp_name'] as $key=>$vaue){
            $imagesize=getimagesize($files['pic']['tmp_name'][$key]);
            if($imagesize[0]>800 or $imagesize[1]>800){
                alertJs('您的图片太大了，建议800*800');
                //清除上传在内存的图片
                $_FILES(null);
                return;
            }
            $savepath ='Uploads/admin/'.date('Y')."/".date('m')."/";
        }

        $upload = new Upload(array(
            'mimes' => $mimes,
            'exts' => $exts,
            'rootPath' => './Public/',
            'savePath' => $savepath,
            'subName'  =>  array('date', 'd'),
        ));

        $info = $upload->upload($files);
        if(!$info) {// 上传错误提示错误信息
            $error = $upload->getError();
            echo "<script>alert('{$error}')</script>";
        }else{// 上传成功
            foreach ($info as $item) {
                $filePath[] = __ROOT__."/Public/".$item['savepath'].$item['savename'];
            }
            $ImgStr = implode("|", $filePath);
            return $ImgStr;
        }
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