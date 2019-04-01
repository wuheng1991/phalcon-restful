<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/18
 * Time: 15:56
 */

namespace Backend\Services;
use Api\Models\GalaxyWechatDeliverMessage;
use Api\Models\GalaxyExpressConfig;
use Api\Models\GalaxyWechatIntegralOrder;
use Api\Models\GalaxyWechatClient;
use Api\Models\GalaxyWechatGatherMessage;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileLogger;

class GalaxyWechatGatherMessageServer extends BaseServer
{
    /**
     * 收货详情
     * @return mixed
     */
    public function getDataService($id){
        $galaxyWechatGatherMessageModel = new GalaxyWechatGatherMessage();

        $where['where'] = 'galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id:';
        $where['value']['galaxy_wechat_integral_order_id'] = $id;


        $galaxyWechatGatherMessageModel->getFindOne('', $where);
        $ret = $galaxyWechatGatherMessageModel->getSucceedResult(1);
        if(!$ret){
            $this->msg = "该订单收货信息不存在";
            return $this->returnData();
        }

        $data = $ret[0];
        $data['id'] = (int)$data['id'];

        $this->msg = "订单收货详情";
        $this->code = 200;
        $this->data = $data;

        return $this->returnData();
    }

    /**
     * 收货编辑
     * @return mixed
     */
    public function saveDataService($id, $params){
        $galaxyWechatIntegralOrderModel = new GalaxyWechatIntegralOrder();
        $galaxyWechatGatherMessageModel = new GalaxyWechatGatherMessage();
        $time = time();

        # 一个汉字占3个字符
        if(isset($params['remarks']) && (!empty($params['remarks']) && strlen($params['remarks']) > 300)){
            $this->msg = "收货备注长度不大于100个汉字";
            return $this->returnData();
        }

        #判断订单是否存在
        $where['where'] = 'id = :id:';
        $where['value']['id'] = $id;

        $galaxyWechatIntegralOrderModel->getFindOne('id, orderid, goods_name, goods_type, galaxy_wechat_client_id', $where);
        $integralOrderRet = $galaxyWechatIntegralOrderModel->getSucceedResult(1);
        if(!$integralOrderRet){
            $this->msg = "该订单不存在";
            return $this->returnData();
        }

        $params['update_time'] = date('Y-m-d H:i:s',$time);
        $gather_where['where'] = 'galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id:';
        $gather_where['value']['galaxy_wechat_integral_order_id'] = $id;

        $galaxyWechatGatherMessageModel->saveData($gather_where,$params);
        if(!$galaxyWechatGatherMessageModel->getErrorResult()){
            $this->msg = "订单收货信息编辑成功";
            $this->code = 200;
            $this->data = true;
        }

        return $this->returnData();

    }
}