<?php
/**
 * Created by PhpStorm.
 * User: Caoyu
 * Date: 2017/9/30
 * Time: 9:24
 * data:求购的自动验证
 */
namespace User\Model;
use Think\Model;
class MetalBuyModel extends Model
{
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)

        array('pkind','require','主要分类不能为空!',0),
        array('kind','require','子分类不能为空!',0),
        array('title','require','品名不能为空!',0),
        array('material','require','材质不能为空!',0),
        array('size','require','规格不能为空!',0),
        array('number','require','需求量不能为空!',0),
        array('address','require','交货地不能为空!',0),
        array('pic','require','图片不能为空!',0),

    );
}