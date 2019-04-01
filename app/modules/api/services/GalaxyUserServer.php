<?php

namespace Api\Services;

use Api\Models\GalaxyWechatClient;
use Api\Models\GalaxyWechatIntegralLog;
use express\express;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 14:43
 */
class GalaxyUserServer extends BaseServer
{
    public function getUserinfo($obj){
        $client_obj = new GalaxyWechatClient();
        $where["where"] = "id =:id: and openid = :openid: and is_deleted = :is_deleted:";
        $where["value"]["id"] = $obj->id;
        $where["value"]["openid"] = $obj->openid;
        $where["value"]["is_deleted"] = 0;
        $obj->userinfo = $client_obj->findone($where,"*");
        return $obj;
    }

    public function registerUser($obj){
        $sms_obj = $obj->di->get("SmsHelper");
        $address = $sms_obj->actionGetAreaByMobile($obj->GalaxyUserServer->mobile);
        $client_obj = new GalaxyWechatClient();
        $where["where"] = "id =:id: and openid = :openid: and vip = :vip:";
        $where["value"]["id"] = $obj->id;
        $where["value"]["openid"] = $obj->openid;
        $where["value"]["vip"] = 0;
        $point = $this->getInitPoint($obj->GalaxyUserServer->mobile);
        $update_data["points"] = $point['points']+$obj->points;
        $update_data["phone"] = $obj->GalaxyUserServer->mobile;
        $update_data["phone_address"] = $address["address"];
        $update_data["vip"] = 1;
        $update_res = $client_obj->updates($where,$update_data);
        if(!$update_res){
            throw new \Exception("注册会员失败");
        }
        if(!empty($point["id"])){
            $init_point = $point['points']+$obj->points;
            $update_log = $this->changePointRecord($point['id'],$obj,$init_point);
            if(!$update_log){
                throw new \Exception("积分初始化失败");
            }
        }
        return $response = [
          "code"=>200,
          "data"=>'',
          "msg"=>"注册会员成功",
        ];
    }

    /**
     * 获取初始化积分
     * @param $cphone
     * @return int
     */
    public function getInitPoint($cphone){
        $point_obj = new GalaxyWechatIntegralLog();
        $where["where"] = "gather_phone = :gather_phone: and operation_type = :operation_type: and integral_state = :integral_state:";
        $where["value"]["gather_phone"] = $cphone;
        $where["value"]["operation_type"] = 0;
        $where["value"]["integral_state"] = 0;
        $point_obj->getFindAll("id,integral,operation_type,integral_state",$where);
        $point = 0;
        $id_arr = array();
        $res = $point_obj->getSucceedResult(1);
        if(!empty($res)){
            foreach($res as $k=>$v){
                $point+=$v['integral'];
                $id_arr[] = $v["id"];
            }
        }
        return array('id'=>$id_arr,'points'=>$point);
    }

    /**
     * 积分记录表积分状态修改为已收入
     * @param $record_id
     * @return bool
     */
    public function changePointRecord($record_id,$obj=null,$behind_integral=0){
        $id_str = implode(",",$record_id);
        $point_obj = new GalaxyWechatIntegralLog();
        $where["where"] = "FIND_IN_SET(id,:id:)";
        $where["value"]["id"] = $id_str;
        $update_arr['integral_state'] = 1;
        if(!empty($obj)){
            $update_arr['nick_name'] = !empty($obj->nick_name)?$obj->nick_name:'';
            $update_arr['behind_integral'] = $behind_integral;
            $update_arr['galaxy_wechat_client_id'] = !empty($obj->id)?$obj->id:'';
            $update_arr['execute_time'] = date('Y-m-d H:i:s',time());
        }
        $point_obj->saveData($where,$update_arr);
        if(!$point_obj->getErrorResult()){
           return true;
        }
        return false;
    }
}