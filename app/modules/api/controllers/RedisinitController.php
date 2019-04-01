<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 16:03
 */
namespace Api\Modules\Api\Controllers;
use Phalcon\Mvc\Controller;
class RedisinitController extends Controller{

    public function initAction(){
      $this->goodsType();
      $this->orderType();
    }

    public function goodsType(){
        $redis = $this->redis;
        $arr = array(0=>"实体礼品",1=>"虚拟礼品");
        $redis->save("goods:type",json_encode($arr),-1);
    }

    public function orderType(){
        $redis = $this->redis;
        $arr = array(1=>"待兑换",2=>"已兑换",3=>"已发货",4=>"已完成",100=>"已取消");
        $redis->save("order:type",json_encode($arr),-1);
    }

}