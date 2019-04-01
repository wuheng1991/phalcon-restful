<?php

namespace Api\Services;

use Api\Models\GalaxyWechatDeliverMessage;
use express\express;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 14:43
 */
class GalaxyWechatDeliverMessageServer extends BaseServer
{
    /**
     * 查询发货信息
     * @param $oid
     * @return array
     */
    public function getExpress($oid)
    {
        $express_obj = new GalaxyWechatDeliverMessage();
        $where['where'] = "galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id:";
        $where['value']["galaxy_wechat_integral_order_id"] = $oid;
        $express_obj->getFindOne("*", $where);
        if (!$express_obj->getErrorResult()) {
            $express_info = $express_obj->getSucceedResult(1);
            if (empty($express_info)) {
                $this->msg = "暂未发货";
                return $this->returnData();
            }
            if ($express_info[0]['express_status'] == 3) {
                $this->code = 200;
                $this->data = $express_info;
                $this->msg = "查询成功";
                return $this->returnData();
            }
            $express_info[0]['express_detail'] = $this->kuaidiApi($express_info[0]);
            $this->code = 200;
            $this->data = $express_info;
            $this->msg = "查询成功";
            return $this->returnData();
        }
        $this->msg = "查询失败";
        return $this->returnData();
    }

    /**
     * 快递接口查询
     * @param $parmas
     * @return string
     */
    public function kuaidiApi($parmas)
    {
        $express = new express();
        $param['com'] = $parmas["express_company_spell"];
        $param['num'] = $parmas["express_number"];
        $express_info = $express->getExpress($param);
        if(isset($express_info['returnCode'])){
            return $express_info['message'];
        }
        $express_detail = json_encode($express_info["data"],JSON_UNESCAPED_UNICODE);
        if (isset($express_info['state']) && $express_info['state'] == 3) {
            $where['where'] = "galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id:";
            $where['value']["galaxy_wechat_integral_order_id"] = $parmas["galaxy_wechat_integral_order_id"];
            $express_obj = new GalaxyWechatDeliverMessage();
            $update["express_detail"] = $express_detail;
            $update["express_status"] = $express_info['state'];
            $express_obj->saveData($where,$update);
        }
        return $express_detail;
    }
}