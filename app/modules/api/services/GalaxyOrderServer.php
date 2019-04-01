<?php

namespace Api\Services;

use Api\Models\GalaxyWechatClient;
use Api\Models\GalaxyWechatGatherMessage;
use Api\Models\GalaxyWechatIntegralGoods;
use Api\Models\GalaxyWechatIntegralLog;
use Api\Models\GalaxyWechatIntegralOrder;
use Api\Models\GalaxyWechatInventoryLog;
use Api\Models\GalaxyWechatAuditMessage;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 14:43
 */
class GalaxyOrderServer extends BaseServer
{
    public function createOrder($parmas, $client)
    {
        $check_obj = new GalaxyGoodsServer();
        $tmp_arr["num"] = $parmas['num'];
        $tmp_arr["goodsid"] = $parmas['goodsid'];
        $goods_info = $check_obj->exchange($tmp_arr, $client->id);
        if (!$goods_info['code']) {
            $this->msg = $goods_info['msg'];
            return $this->returnData();
        }
        $redis_exist_res = $this->redis->hExists("goods:num", $parmas['goodsid']);
        if (empty($redis_exist_res)) {
            $this->msg = "礼品已被抢光了";
            return $this->returnData();
        }
//        $redis_goods_num = $this->redis->hGet("goods:num", $parmas['goodsid']);
//        if ($redis_goods_num < $parmas['num']) {
//            $this->msg = "礼品库存不足了";
//            return $this->returnData();
//        }
        $redis_res = $this->redis->hIncrBy("goods:num", $parmas['goodsid'], -$parmas['num']);
        if ($redis_res < 0) {
            $this->redis->hIncrBy("goods:num", $parmas['goodsid'], $parmas['num']);
            $this->msg = "礼品库存不足了";
            return $this->returnData();
        }
        $this->db->begin();
        $inventory_log = $this->inventoryLog($parmas['goodsid'], $redis_res+$parmas['num'], $parmas['num']);
        if (empty($inventory_log)) {
            $this->redis->hIncrBy("goods:num", $parmas['goodsid'], $parmas['num']);
            $this->msg = "创建库存记录失败";
            $this->db->rollback();
            return $this->returnData();
        }
        $order["goods_name"] = $goods_info['data'][0]['goods_name'];
        $order["goods_price"] = $goods_info['data'][0]['integral_price'];
        $order["goods_type"] = $goods_info['data'][0]['goods_type'];
        $order["litimg_url"] = $goods_info['data'][0]['litimg_url'];
        $order["galaxy_wechat_classify_id"] = $goods_info['data'][0]['galaxy_wechat_classify_id'];
        $order["galaxy_wechat_client_id"] = $client->id;
        $order["galaxy_wechat_integral_goods_id"] = $parmas['goodsid'];
        $order["orderid"] = $this->createOrderNum();
        $order["nick_name"] = $client->nick_name;
        $order["name"] = $client->name;
        $order["getin_integral"] = $goods_info['data'][0]['integral_price'] * $parmas['num'];
        $order["phone"] = $client->phone;
        $order["amount"] = $parmas['num'];
        $order_obj = new GalaxyWechatIntegralOrder();
        $order_obj->createData($order);
        if ($order_obj->getErrorResult()) {
            $this->redis->hIncrBy("goods:num", $parmas['goodsid'], $parmas['num']);
            $this->msg = "创建订单失败";
            $this->db->rollback();
            return $this->returnData();
        }
        $order_id = $order_obj->getSucceedResult();
        if($goods_info['data'][0]['goods_type'] == 0){
            $gather_res = $this->createGatherMsg($parmas, $order_id);
            if (empty($gather_res)) {
                $this->redis->hIncrBy("goods:num", $parmas['goodsid'], $parmas['num']);
                $this->msg = "创建收货信息失败";
                $this->db->rollback();
                return $this->returnData();
            }
        }

        $this->db->commit();
        $this->redis->save("order:expire:$order_id",time()+1800,30*60);
        $this->code = 200;
        $this->data = (int)$order_id;
        $this->msg = "创建订单成功";
        return $this->returnData();
    }

    /**
     * 创建收货记录关联
     * @param $parmas
     * @param $order_id
     * @return bool
     */
    public function createGatherMsg($parmas, $order_id)
    {
        $gather_info["detailed_address"] = $parmas['detailed_address'];
        $gather_info["detailed_name"] = $parmas['detailed_name'];
        $gather_info["detailed_phone"] = $parmas['detailed_phone'];
        $gather_info["galaxy_wechat_integral_order_id"] = $order_id;
        $msg_obj = new GalaxyWechatGatherMessage();
        $msg_obj->createData($gather_info);
        if ($msg_obj->getErrorResult()) {
            return false;
        }
        return true;
    }

    /**
     * 库存操作日志
     * @param $goodsid
     * @param $front_inventory
     * @param $inventory 减少则传负值
     * @return bool
     */
    public function inventoryLog($goodsid, $front_inventory, $inventory)
    {
        $inventory_obj = new GalaxyWechatInventoryLog();
        $data['front_inventory'] = $front_inventory;
        $data['behind_inventory'] = $front_inventory + $inventory;
        if ($inventory < 0) {
            $data['operation_type'] = 1;
        }
        $data['inventory'] = $inventory;
        $data['galaxy_wechat_integral_goods_id'] = $goodsid;
        $inventory_obj->createData($data);
        if (empty($inventory_obj->getErrorResult())) {
            return true;
        }
        return false;
    }

    /**
     * 创建订单号
     * @return string
     */
    public function createOrderNum()
    {
        //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
        $order_id_main = date('YmdHis') . rand(10000000, 99999999);
        //订单号码主体长度
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;
        for ($i = 0; $i < $order_id_len; $i++) {
            $order_id_sum += (int)(substr($order_id_main, $i, 1));
        }
        //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
        $order_id = "WXJF" . $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
        return $order_id;
    }

    /**
     * 获取礼品信息
     * @param $goodsid
     * @return array
     */
    public function getGoods($goodsid)
    {
        $good_obj = new GalaxyWechatIntegralGoods();
        $where["where"] = "id = :id: and is_delete=:is_delete:";
        $where["value"]["id"] = $goodsid;
        $where["value"]["is_delete"] = 0;
        $good_obj->getFindOne("*", $where);
        return $good_obj->getSucceedResult(1);
    }

    /**
     * 订单兑换
     * @param $oid
     * @return array
     */
    public function exchange($oid)
    {
        $order_info = GalaxyWechatIntegralOrder::find($oid)->toArray();
        if (empty($order_info) || $order_info[0]['order_type'] != 1) {
            $this->msg = "订单异常";
            return $this->returnData();
        }
        $client_info = GalaxyWechatClient::find($order_info[0]['galaxy_wechat_client_id'])->toArray();
        if (empty($client_info)) {
            $this->msg = "用户不存在";
            return $this->returnData();
        }
        if ($client_info[0]['points'] < $order_info[0]['getin_integral']) {
            $this->msg = "积分不足";
            return $this->returnData();
        }
        $this->db->begin();
        $where["where"] = "id = :id:";
        $where["value"]["id"] = $oid;
        $update_data["order_type"] = 2;
        $update_data["getin_time"] = date("Y-m-d H:i:s", time());
        $order_obj = new GalaxyWechatIntegralOrder();
        $order_obj->saveData($where, $update_data);
        if ($order_obj->getErrorResult()) {
            $this->msg = "兑换失败";
            $this->db->rollback();
            return $this->returnData();
        }
        $client_obj = GalaxyWechatClient::findFirstById($order_info[0]['galaxy_wechat_client_id']);
        $client_obj->points = $client_info[0]['points'] - $order_info[0]['getin_integral'];
        if (!$client_obj->update()) {
            $this->msg = "兑换失败";
            $this->db->rollback();
            return $this->returnData();
        }
        $goods = GalaxyWechatIntegralGoods::findFirstById($order_info[0]['galaxy_wechat_integral_goods_id']);
        $goods->sales_volume = $goods->sales_volume + $order_info[0]['amount'];
        if (!$goods->update()) {
            $this->msg = "兑换失败";
            $this->db->rollback();
            return $this->returnData();
        }
        $remarks = "兑换礼品：".$goods->goods_name;
        $integraLog_res = $this->integralLog($order_info[0]['galaxy_wechat_client_id'], $oid, $client_info[0]['points'], $client_obj->points,$remarks);
        if (empty($integraLog_res)) {
            $this->msg = "兑换失败";
            $this->db->rollback();
            return $this->returnData();
        }
        $this->db->commit();
        $this->code = 200;
        $this->msg = "兑换成功";
        return $this->returnData();
    }

    /**
     * 填写积分记录
     * @param $cid 
     * @param $oid
     * @param $front_integral
     * @param $behind_integral
     * @param $remarks  积分记录备注
     * @return bool
     */
    public function integralLog($cid, $oid, $front_integral, $behind_integral,$remarks = '')
    {
        //获取用户的信息
        $galaxyWechatClient = new GalaxyWechatClient();
        $where["where"] = "id = :id: and is_deleted = :is_deleted:";
        $where["value"]["id"] = $cid;
        $where["value"]["is_deleted"] = 0;
        $clientInfo = $galaxyWechatClient->findone($where,"*");
        
        if(!empty($clientInfo)){
            $data['nick_name']  = $clientInfo->nick_name;
            $data['gather_phone'] = $clientInfo->phone;
        }

        $integral_obj = new GalaxyWechatIntegralLog();
        $data['integral_type'] = 3;
        $data['integral'] = $front_integral - $behind_integral;
        $data['operation_type'] = 1;
        $data['integral_type'] = 3;
        $data['integral_state'] = 2;
        $data['front_integral'] = $front_integral;
        $data['behind_integral'] = $behind_integral;
        $data['ip'] = $_SERVER["REMOTE_ADDR"];
        $data['remarks'] = !empty($remarks)?$remarks:'积分兑换';
        $data['galaxy_wechat_client_id'] = $cid;
        $data['execute_time'] = date("Y-m-d H:i:s", time());
        $data['galaxy_wechat_integral_order_id'] = $oid;
        $integral_obj->createData($data);
        if (empty($integral_obj->getErrorResult())) {
            return true;
        }
        return false;
    }

    /**
     * 查询客户全部订单
     * @param $parmas
     * @return array
     */
    public function getAllOrder($parmas)
    {
        $order_obj = new GalaxyWechatIntegralOrder();
        $where["where"] = "galaxy_wechat_client_id = :galaxy_wechat_client_id:";
        $where["value"]["galaxy_wechat_client_id"] = $parmas["cid"];

        //判断状态是否为空
        if (!empty($parmas['order_type'])) {
            //有可能为逗号拼接的状态，采用in方式
            $where["where"] .= " and order_type in(".$parmas['order_type'].") ";
            // $where["value"]["order_type"] = ;
        }
        $toBy["orderby"] = "id Desc";
        $order_obj->getFindAll("*", $where,'',$toBy);
        if (!$order_obj->getErrorResult()) {
            $order_info = $order_obj->getSucceedResult(1);
            if (!empty($order_info)) {
                $order_type = json_decode($this->redis->get("order:type"), true);
                foreach ($order_info as $k => $v) {
                    $order_info[$k]["order_type_str"] = !empty($order_type[$v["order_type"]])?$order_type[$v["order_type"]]:"";
                }
            }
            $this->code = 200;
            $this->data = $order_info;
            $this->msg = "查询成功";
        }
        return $this->returnData();
    }

    /**
     * 查询单个订单
     * @param $oid
     * @return array
     */
    public function getOrder($oid)
    {
        $sql = "SELECT
                    orders.id,
                    orders.orderid,
                    orders.order_type,
                    orders.create_time,
                    orders.getin_time,
                    orders.getin_integral,
                    orders.cancel_time,
                    orders.amount,
                    address.detailed_address,
                    address.detailed_name,
                    address.detailed_phone,
                    orders.litimg_url,
                    goods.integral_price,
                    orders.goods_name,
                    orders.goods_type
                FROM
                    galaxy_wechat_integral_order AS orders
                LEFT JOIN galaxy_wechat_gather_message AS address ON orders.id = address.galaxy_wechat_integral_order_id
                LEFT JOIN galaxy_wechat_integral_goods as goods on orders.galaxy_wechat_integral_goods_id = goods.id where orders.id = ".(int)$oid;
        $order_obj = new GalaxyWechatIntegralOrder();

        $order_info = $order_obj->querysql($sql);
        if (empty($order_info)) {
            $this->msg = "订单不存在";
            return $this->returnData();
        }
        if($order_info[0]["order_type"] == 1){
            $order_expire = $this->redis->get("order:expire:$oid");
            if($order_expire){
                $order_info[0]["exprie"] = (int)$order_expire-time();
            }else{
                $order_info[0]["exprie"] = 0;
            }
        }
        
        //查询核销的信息
        $galaxyWechatAuditMessage = new GalaxyWechatAuditMessage();
        $sql = 'select * from galaxy_wechat_audit_message where galaxy_wechat_integral_order_id = '.$order_info[0]['id'];
        $auditData = $galaxyWechatAuditMessage->querysql($sql);
        // $auditWhere["where"] = "galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id:";
        // $auditWhere["value"]["galaxy_wechat_integral_order_id"] = $order_info[0]['id'];
        // $auditData = $galaxyWechatAuditMessage->getFindOne('*',$auditWhere);

        //初始化核销时间，判断是否为空
        $auditTime = '';
        if(isset($auditData[0]['create_time']) && !empty($auditData[0]['create_time'])){
            $auditTime = $auditData[0]['create_time'];
        }
        $order_info[0]['audit_time'] = $auditTime;
        $order_type = json_decode($this->redis->get("order:type"), true);
        $goods_type = json_decode($this->redis->get("goods:type"), true);
        $order_info[0]["order_type_str"] = !empty($order_type[$order_info[0]["order_type"]])?$order_type[$order_info[0]["order_type"]]:"";
        $order_info[0]["goods_type"] = !empty($goods_type[$order_info[0]["goods_type"]])?$goods_type[$order_info[0]["goods_type"]]:"";
        $this->code = 200;
        $this->data = $order_info;
        $this->msg = "查询成功";
        return $this->returnData();
    }
}