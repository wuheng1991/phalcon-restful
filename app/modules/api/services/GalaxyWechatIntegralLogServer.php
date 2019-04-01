<?php

namespace Api\Services;

use Api\Models\GalaxyWechatIntegralLog;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 14:43
 */
class GalaxyWechatIntegralLogServer extends BaseServer
{
    /**
     * 获取积分记录
     * @param $type
     * @param $cid
     * @return array
     */
    public function getIntegralList($type, $cid)
    {
        $log_obj = new GalaxyWechatIntegralLog();
        $where['where'] = "galaxy_wechat_client_id = :galaxy_wechat_client_id: and integral_state = :integral_state:";
        $where['value']['galaxy_wechat_client_id'] = $cid;
        $where['value']['integral_state'] = (int)$type;
        $toBy["orderby"] = 'id desc';
        $log_obj->getFindAll("*", $where, '', $toBy);
        if (!$log_obj->getErrorResult()) {
            $list = $log_obj->getSucceedResult(1);
            $count = 0;
            foreach ($list as $v){
                $count += $v["integral"];
            }
            $this->code = 200;
            $this->data["list"] = $list;
            $this->data["count"] = $count;
            $this->msg = "查询成功";
        }
        return $this->returnData();
    }
}