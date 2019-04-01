<?php

namespace Api\Services;

use Api\Models\GalaxyWechatClient;
use Api\Models\GalaxyWechatGatherAddress;
use Api\Models\GalaxyWechatIntegralGoods;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 14:43
 */
class GalaxyGoodsServer extends BaseServer
{
    /**
     * 公众号-礼品列表
     * @param $params
     * @return array
     */
    public function goodsList($params)
    {
        $order_arr = array('integral_price','sales_volume');
        $tmp_order = "";
        $order["orderby"] = "sort Desc";
        foreach($order_arr as $k=>$v){
            if(!empty($params[$v])){
                $tmp_order.=$v.' '.$params[$v].',';
            }
        }
        if(!empty($tmp_order)){
            $order["orderby"] = substr($tmp_order,0,strlen($tmp_order)-1);
        }
        $goods_obj = new GalaxyWechatIntegralGoods();
        $where["where"] = "state = :state: and is_delete =:is_delete:";
        $where["value"]["state"] = 1;
        $where["value"]["is_delete"] = 0;
        if(isset($params['type']) && $params['type'] !== ""){
            $where["where"] .=" and goods_type = :goods_type:";
            $where["value"]["goods_type"] = intval($params['type']);
        }
        $goods_obj->getFindAll("id,goods_name,integral_price,sales_volume,market_price,litimg_url,goods_type,state,goods_outline,galaxy_wechat_classify_id",$where,array(),$order);
           if(!$goods_obj->getErrorResult()){
               $goods_num = $this->redis->hGetAll("goods:num");
               $goods_info = $goods_obj->getSucceedResult(1);
               foreach($goods_info as $k=>$v){
                   $goods_info[$k]['inventory'] = isset($goods_num[$v['id']])?(int)$goods_num[$v['id']]:0;
               }
               $this->code=200;
               $this->data=$goods_info;
               $this->msg ="查询成功";
           }

        return $this->returnData();
    }

    /**
     * 公众号-礼品详情
     * @param $id
     * @return array
     */
    public function goodsDetail($id){
        $goods_obj = new GalaxyWechatIntegralGoods();
        $where["where"] = "state = :state: and is_delete =:is_delete: and id = :id:";
        $where["value"]["state"] = 1;
        $where["value"]["is_delete"] = 0;
        $where["value"]["id"] = $id;
        $field = "id,goods_name,goods_type,integral_price,inventory,goods_img_url,goods_outline,market_price,litimg_url,goods_describe";
        $goods_obj->getFindOne($field,$where);
        if(!$goods_obj->getErrorResult()){
            $res = $goods_obj->getSucceedResult(1);
            if(!empty($res)){
                $res[0]["inventory"] = 0;
                $goods_num = $this->redis->hGet("goods:num",$res[0]["id"]);
                if(!empty($goods_num)){
                    $res[0]["inventory"] = $goods_num;
                }
                $type = json_decode($this->redis->get("goods:type"),true);
                $res[0]["goods_type"] = $type[$res[0]["goods_type"]];
                $this->code=200;
                $this->data=$res;
                $this->msg ="查询成功";
            }else{
                $this->msg ="礼品不存在";
            }
        }
        return $this->returnData();
    }

    /**
     * 判断用户兑换的积分和剩余库存是否符合要求
     * @param $parmas
     * @param $cid
     * @return array|bool
     */
    public function exchange($parmas,$cid){
        $inventory = $this->redis->hGet("goods:num",$parmas["goodsid"]);
        if($parmas["num"]>(int)$inventory){
            $res['code'] = 0;
            $res['msg'] = "礼品库存不足了";
            return $res;
        }
        $client_info = GalaxyWechatClient::find($cid);
        if(empty($client_info[0])){
            $res['code'] = 0;
            $res['msg'] = "请先注册会员";
            return $res;
        }
        $client_info = $client_info->toArray();
        $goods_info = GalaxyWechatIntegralGoods::find($parmas["goodsid"]);
        if(empty($goods_info[0])){
            $res['code'] = 0;
            $res['msg'] = "礼品已经下架";
            return $res;
        }
        $goods_info = $goods_info->toArray();
        $all_integral_price = $parmas["num"] * $goods_info[0]["integral_price"];
        if($client_info[0]["points"] < $all_integral_price){
            $res['code'] = 0;
            $res['msg'] = "积分不足";
            return $res;
        }
        return array('code'=>200,'data'=>$goods_info,'msg'=>'ok');
    }

    /**
     * 设置礼品库存
     * @param $goodid
     * @param $num
     */
    public function setGoodsNum($goodid,$num){
       return $this->redis->hSet("goods:num",$goodid,$num);
    }

    /**
     * 增加/减少礼品库存
     * @param $goodid
     * @param $num
     * @return mixed
     */
    public function goodsNumIncr($goodid,$num){
        return $this->redis->hIncrBy("goods:num",$goodid,$num);
    }

}