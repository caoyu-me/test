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
class CartController extends HomeController{
    protected $Cart_model;
    protected $Food_model;
    public function _initialize(){
        parent::_initialize();
        $this->Cart_model = M("cart");
        $this->Food_model = M("food");
    }
    public  function Index()
    {
        $user=$this->users;
//        $_SESSION['user_id']=array(
//                'has_login'=>1,
//                'user_id'=>2,
//                'user_name'=>'caoyu',
//                'last_login'=>'',
//                'last_ip'=>'127.0.0.1',
//                'store_id'=>'2',
//        );
//        $_SESSION['user_id']=array('has_login'=>'1','info'=>array('user_id'=>2,'user_name'=>'caoyu','reg_time'=>'1516325706','last_login'=>'1516325706','last_ip'=>'127.0.0.1','store_id'=>1),'privilege'=>'');
//
//        dump($this->users=array('has_login'=>'1','info'=>array('user_id'=>2,'user_name'=>'caoyu','reg_time'=>'1516325706','last_login'=>'1516325706','last_ip'=>'127.0.0.1','store_id'=>1),'privilege'=>''));

//        die;

        $store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
        $carts = $this->_get_carts($store_id);

        if (empty($carts))
        {
            $this->_cart_empty();
            return;
        }

        $this->assign('carts',$carts);
        $this->display();
    }
    /**
     *    放入商品(根据不同的请求方式给出不同的返回结果)
     *
     *    @author    Garbin
     *    @return    void
     */
   public function add()
    {
        //商品id
        $spec_id   =  I('post.spec_id') ? intval(I('post.spec_id')) : 0;
        //数量
        $quantity   =  I('post.quantity') ? intval( I('post.quantity') ) : 0;
        if (!$spec_id || !$quantity)
        {
            return;
        }

        /* 是否有商品 */
        //商品的参数
//        $spec_info  =  $spec_model->get(array(
//            'fields'        => 'g.store_id, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.default_image, gs.spec_1, gs.spec_2, gs.stock, gs.price',
//            'conditions'    => $spec_id,
//            'join'          => 'belongs_to_goods',
//        ));
        $spec_info=$this->Food_model->where('id='.$spec_id)->find();

        if (!$spec_info)
        {
            $this->ajaxReturn(array('err'=>0,'msg'=>'此商品不存在'));
            /* 商品不存在 */
            return;
        }
        /* 如果是自己店铺的商品，则不能购买 */
        if ($this->users['manage_store'])
        {
            if ($spec_info['store_id'] == $this->users['manage_store'])
            {
                $this->ajaxReturn(array('err'=>0,'msg'=>'您不能购买自己店铺的商品'));

                return;
            }
        }

        /* 是否添加过 */
        $item_info  = $this->Cart_model->where("goods_id=".$spec_id)->find();
        if (!empty($item_info))
        {
            $this->ajaxReturn(array('err'=>0,'msg'=>'该商品已经在购物车中了'));
            return;
        }
//库存
        if ($quantity > $spec_info['number'])
        {
            $this->ajaxReturn(array('err'=>0,'msg'=>'没有足够的商品'));
            return;
        }

/*        $spec_1 = $spec_info['spec_name_1'] ? $spec_info['spec_name_1'] . ':' . $spec_info['spec_1'] : $spec_info['spec_1'];
        $spec_2 = $spec_info['spec_name_2'] ? $spec_info['spec_name_2'] . ':' . $spec_info['spec_2'] : $spec_info['spec_2'];

        $specification = $spec_1 . ' ' . $spec_2;*/

        /* 将商品加入购物车 */
        $cart_item = array(
            'user_id'       => $this->users['info']['user_id'],
//            'session_id'    => SESS_ID,
            'store_id'      => $spec_info['store'],
            //规格
            'goods_id'       => $spec_id,
            //详细属性
//            'goods_id'      => $spec_info['goods'],
            'goods_name'    => addslashes($spec_info['name']),
//            'specification' => addslashes(trim($specification)),
            'price'         => $spec_info['price'],
            //数量
            'quantity'      => $quantity,
            'goods_image'   => addslashes($spec_info['default_image']),
        );

        /* 添加并返回购物车统计即可 */
        $this->Cart_model->add($cart_item);
        $cart_status = $this->_get_cart_status();

//        /* 更新被添加进购物车的次数 */
//        $model_goodsstatistics =& m('goodsstatistics');
//        $model_goodsstatistics->edit($spec_info['goods_id'], 'carts=carts+1');
       //返回购物车状态
        $this->ajaxReturn(array(
          'err'=>1,'cart'=>$cart_status['status'],'msg'=>'添加购物车成功'));
    }
    /**
     *    更新购物车中商品的数量，以商品为单位，AJAX更新
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    public function update()
    {
        $goods_id  = isset($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;
        $quantity = isset($_POST['quantity'])? intval($_POST['quantity']): 0;
        if (!$goods_id || !$quantity)
        {
            /* 不合法的请求 */
            return;
        }
        /* 判断库存是否足够 */
        $spec_info  =  $this->Food_model-> where('id='.$goods_id)->find();
//        if (empty($spec_info))
//        {
//            /* 没有该规格 */
//            $this->json_error('no_such_spec');
//            return;
//        }

        if ($quantity > $spec_info['number'])
        {
            /* 数量有限 */
            $this->ajaxReturn(array('err'=>0,'msg'=>'抱歉，库存不足'));
            return;
        }

        /* 修改数量 */
        $where = "goods_id=".$goods_id;
        $model_cart =$this->Cart_model;

        /* 获取购物车中的信息，用于获取价格并计算小计 */
        $cart_spec_info = $model_cart->where($where)->find();
        if (empty($cart_spec_info))
        {
            /* 并没有添加该商品到购物车 */
            return;
        }

        $store_id = $cart_spec_info['store_id'];

        /* 修改数量 */
        $data['quantity'] =$quantity;
        $model_cart->where($where)->save($data);

        /* 小计 */
        $subtotal   =   $quantity * $cart_spec_info['price'];

        /* 返回JSON结果 */
        $cart_status = $this->_get_cart_status();
        $this->ajaxReturn(array(
            'err'=>1,'cart'=>$cart_status['status'],'msg'=>'添加购物车成功','subtotal'=>$subtotal));
//        $this->json_result(array(
//            'cart'      =>  $cart_status['status'],                     //返回总的购物车状态
//            'subtotal'  =>  $subtotal,                                  //小计
//            'amount'    =>  $cart_status['carts'][$store_id]['amount']  //店铺购物车总计
//        ), 'update_item_successed');
    }
    /**
     *    丢弃商品
     *
     *    @author    Garbin
     *    @return    void
     */
   public function drop()
    {
        /* 传入rec_id，删除并返回购物车统计即可 */
        //商品id
        $rec_id   =  I('post.spec_id') ? intval(I('post.spec_id')) : 0;
        if (!$rec_id)
        {
            return;
        }

        /* 从购物车中删除 */
        //先保存一份
        $dropped_data=$this->Cart_model->where('rec_id=' . $rec_id )->find();
        $droped_rows = $this->Cart_model->where('rec_id=' . $rec_id )->delete();
        if (!$droped_rows)
        {
            return;
        }

        /* 返回结果 */
        $store_id     = $dropped_data[$rec_id]['store_id'];
        $cart_status = $this->_get_cart_status();

        $this->ajaxReturn(array(
            'err'=>1,'cart'=>$cart_status['status'],'msg'=>'删除购物车成功'));

//        $this->json_result(array(
//            'cart'  =>  $cart_status['status'],                      //返回总的购物车状态
//            'amount'=>  $cart_status['carts'][$store_id]['amount']   //返回指定店铺的购物车状态
//        ),'drop_item_successed');
    }
    /**
     *    购物车为空
     *
     *    @author    Garbin
     *    @return    void
     */
    function _cart_empty()
    {
        return('购物车暂时为空');
    }
    /**
     *    获取购物车状态
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_cart_status()
    {
        /* 默认的返回格式 */
        $data = array(
            'status'    =>  array(
                'quantity'  =>  0,      //总数量
                'amount'    =>  0,      //总金额
                'kinds'     =>  0,      //总种类
            ),
            'carts'     =>  array(),    //购物车列表，包含每个购物车的状态
        );

        /* 获取所有购物车 */
        $carts = $this->_get_carts();

        if (empty($carts))
        {
            return $data;
        }
        $data['carts']  =   $carts;
        foreach ($carts as $store_id => $cart)
        {
            $data['status']['quantity'] += $cart['quantity'];
            $data['status']['amount']   += $cart['amount'];
            $data['status']['kinds']    += $cart['kinds'];
        }

        return $data;
    }
    /**
     *    以购物车为单位获取购物车列表及商品项
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_carts($store_id = 0)
    {
        $carts = array();

        /* 获取所有购物车中的内容 */
        //店铺id
        $where_store_id = $store_id ? ' AND a.store_id=' . $store_id : '';

        /* 只有是自己购物车的项目才能购买 */
        // 用户id
        $where_user_id = $this->users['info']['user_id'] ? " a.user_id=" . $this->users['info']['user_id'] : '';

        $cart_model =$this->Cart_model;
        $cart_items = $cart_model
            ->alias("a")
            ->join("__FOOD__ b ON a.goods_id = b.id")
            ->join('__STORE__ c on a.store_id = c.store_id')
            ->field('a.*,b.name,b.store,c.store_name')->where($where_user_id.$where_store_id)->select();

        if (empty($cart_items))
        {
            return $carts;
        }
//        dump($cart_items);
        $kinds = array();
        foreach ($cart_items as $item)
        {
            /* 小计 */
            $item['subtotal']   = $item['price'] * $item['quantity'];
            $kinds[$item['store_id']][$item['goods_id']] = 1;

            /* 以店铺ID为索引 */
            empty($item['goods_image']) && $item['goods_image'] = default_images();
            $carts[$item['store_id']]['store_name'] = $item['store_name'];
            $carts[$item['store_id']]['amount']     += $item['subtotal'];   //各店铺的总金额
            $carts[$item['store_id']]['quantity']   += $item['quantity'];   //各店铺的总数量
            $carts[$item['store_id']]['goods'][]    = $item;

        }

        foreach ($carts as $_store_id => $cart)
        {
            //更具商品id 多少种
            $carts[$_store_id]['kinds'] =   count(array_keys($kinds[$_store_id]));  //各店铺的商品种类数
        }

        return $carts;
    }


}