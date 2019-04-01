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
class GalaxyAddressServer extends BaseServer
{
    public function getAddress($cid){
        $address_obj = new GalaxyWechatGatherAddress();
        $select = "*";
        $where["where"] = 'galaxy_wechat_client_id = :galaxy_wechat_client_id: and is_delete = :is_delete:';
        $where["value"]["galaxy_wechat_client_id"] = $cid;
        $where["value"]["is_delete"] = 0;
        $toBy['orderby'] = " is_default desc";
        $address_obj->getFindAll($select,$where,array(),$toBy);
        if(!$address_obj->getErrorResult()){
            //业务判断地址是否为空
//            if(!empty($address_obj->getSucceedResult(1))){
                $this->msg = '查询成功';
                $this->code = 200;
                $this->data = $address_obj->getSucceedResult(1);
//            }else{
//                $this->msg ="地址为空";
//                $this->data = $address_obj->getSucceedResult(1);
//            }
        }
        return $this->returnData();
    }

    public function addAddress($parmas){
        $address_obj = new GalaxyWechatGatherAddress();
        $address_obj->createData($parmas);
        if(!$address_obj->getErrorResult()){
            $this->code=200;
            $this->data=$address_obj->getSucceedResult();
            $this->msg ="新增成功";
        }
        return $this->returnData();
    }

    public function updateAddress($parmas,$aid){
        $address_obj = new GalaxyWechatGatherAddress();
        $where["where"] = "id = :id:";
        $where["value"]["id"] = $aid;
        $address_obj->saveData($where,$parmas);
        if(!$address_obj->getErrorResult()){
            $this->code=200;
            $this->data=$address_obj->getSucceedResult();
            $this->msg ="修改成功";
        }
        return $this->returnData();
    }

    public function deleteAddress($aid){
        $address_obj = new GalaxyWechatGatherAddress();
        $where["where"] = "id = :id:";
        $where["value"]["id"] = $aid;
        $parmas["is_delete"] = 1;
        $address_obj->saveData($where,$parmas);
        if(!$address_obj->getErrorResult()){
            $this->code=200;
            $this->data=$address_obj->getSucceedResult();
            $this->msg ="删除成功";
        }
        return $this->returnData();
    }

    public function getoneAddress($aid){

        $address_obj = new GalaxyWechatGatherAddress();
        $select = "*";
        $where["where"] = 'id = :id: and is_delete = :is_delete:';
        $where["value"]["id"] = $aid;
        $where["value"]["is_delete"] = 0;
        $toBy['orderby'] = " is_default desc";
        $address_obj->getFindOne($select,$where);
        if(!$address_obj->getErrorResult()){
            $addressData = $address_obj->getSucceedResult(1);
            //业务判断地址是否为空
            if(!empty($addressData)){
                $this->msg = '查询成功';
                $this->code = 200;
                $this->data = $addressData[0];
            }else{
                $this->msg ="地址为空";
                $this->data = $addressData;
            }
        }
        return $this->returnData();

        // $address_info = GalaxyWechatGatherAddress::findFirstById($aid)->toArray();
        // $this->code =200;
        // $this->data = $address_info;
        // $this->msg = "查询成功";
        // return $this->returnData();
    }
}