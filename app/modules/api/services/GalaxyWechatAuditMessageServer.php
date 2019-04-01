<?php

namespace Api\Services;
use Api\Models\GalaxyWechatAuditMessage;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 14:43
 */
class GalaxyWechatAuditMessageServer extends BaseServer
{

    /**
     * 获取核销记录
     * @param $oid
     * @return array
     */
    public function getAudit($oid){
        $audit_obj = new GalaxyWechatAuditMessage();
        $where["where"] = "galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id: and is_delete=:is_delete:";
        $where["value"]["galaxy_wechat_integral_order_id"] = $oid;
        $where["value"]["is_delete"] = 0;
        $audit_obj->getFindOne("*",$where);
        if(!$audit_obj->getErrorResult()){
            $this->code = 200;
            $this->data = $audit_obj->getSucceedResult(1);
            $this->msg = "查询成功";
            return $this->returnData();
        }
        $this->msg = "查询失败";
        return $this->returnData();
    }
}