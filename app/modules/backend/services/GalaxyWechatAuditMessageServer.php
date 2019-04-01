<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/29
 * Time: 18:58
 */
namespace Backend\Services;
use Api\Models\GalaxyWechatAuditMessage;
use Api\Models\GalaxyWechatIntegralOrder;

class GalaxyWechatAuditMessageServer extends BaseServer
{
    /**
     * 核销增添
     * @return mixed
     */
    public function addDataService($id, $params, $userinfo){
        $galaxyWechatAuditMessageModel = new GalaxyWechatAuditMessage();
        $galaxyWechatIntegralOrderModel = new GalaxyWechatIntegralOrder();
        $time = time();

        if(isset($params['audit_img_url']) && empty($params['audit_img_url'])){
            $this->msg = "核销凭证图片链接地址不饿为空";
            return $this->returnData();
        }

        # 一个汉字占3个字符
        if(isset($params['remarks']) && (!empty($params['remarks']) && strlen($params['remarks']) > 90)){
            $this->msg = "备注说明不能大于30汉字";
            return $this->returnData();
        }

        #判断订单是否存在
        $order_where['where'] = 'id = :id:';
        $order_where['value']['id'] = $id;

        $galaxyWechatIntegralOrderModel->getFindOne('id, orderid, order_type, goods_name, goods_type, galaxy_wechat_client_id', $order_where);
        $integralOrderRet = $galaxyWechatIntegralOrderModel->getSucceedResult(1);
        if(!$integralOrderRet){
            $this->msg = "该订单不存在";
            return $this->returnData();
        }

        #判断是否已经核销
        $deliver_audit_where['where'] = "galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id:";
        $deliver_audit_where['value']['galaxy_wechat_integral_order_id'] = $id;

        $galaxyWechatAuditMessageModel->getFindOne('id', $deliver_audit_where);
        $auditMessageRet = $galaxyWechatAuditMessageModel->getSucceedResult(1);
        if($auditMessageRet){
            $this->msg = "该订单已创建核销";
            return $this->returnData();
        }

        #核销信息
        $params['audit_name'] = isset($userinfo['username']) ? $userinfo['username'] : '';
        $params['galaxy_admin_id'] = isset($userinfo['id']) ? $userinfo['id'] : 0;
        $params['galaxy_wechat_integral_order_id'] = $id;

        $ret = $galaxyWechatAuditMessageModel->createData($params);
        if($ret) {
            #若订单状态为“已发货”-3，修改状态为“已完成”-4
            if($integralOrderRet[0]['order_type'] == '3'){
                $order_params['order_type'] = 4;
            }

            $order_params['update_time'] = date('Y-m-d H:i:s',$time);
            $galaxyWechatIntegralOrderModel->saveData($order_where,$order_params);
            if($galaxyWechatIntegralOrderModel->getErrorResult()){
                $this->msg = "订单状态修改失败";
                return $this->returnData();
            }

            $this->msg = "核销增添成功";
            $this->code = 200;
            $this->data = true;
        }

        return $this->returnData();
    }

    /**
     * 核销编辑
     * @return mixed
     */
    public function saveDataService($id, $params, $userinfo){
        $galaxyWechatAuditMessageModel = new GalaxyWechatAuditMessage();
        $galaxyWechatIntegralOrderModel = new GalaxyWechatIntegralOrder();
        $time = time();

        $where['where'] = 'galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id: AND is_delete = :is_delete:';
        $where['value']['galaxy_wechat_integral_order_id'] = $id;
        $where['value']['is_delete'] = 0;

        $galaxyWechatAuditMessageModel->getFindOne('', $where);
        $ret = $galaxyWechatAuditMessageModel->getSucceedResult(1);
        if(!$ret){
            $this->msg = "该核销信息不存在";
            return $this->returnData();
        }

        if(isset($params['audit_img_url']) && empty($params['audit_img_url'])){
            $this->msg = "核销凭证图片链接地址不饿为空";
            return $this->returnData();
        }

        # 一个汉字占3个字符
        if(isset($params['remarks']) && (!empty($params['remarks']) && strlen($params['remarks']) > 90)){
            $this->msg = "备注说明不能大于30汉字";
            return $this->returnData();
        }

        #判断订单是否存在
        $order_where['where'] = 'id = :id:';
        $order_where['value']['id'] = $id;

        $galaxyWechatIntegralOrderModel->getFindOne('id, orderid, order_type, goods_name, goods_type, galaxy_wechat_client_id', $order_where);
        $integralOrderRet = $galaxyWechatIntegralOrderModel->getSucceedResult(1);
        if(!$integralOrderRet){
            $this->msg = "该订单不存在";
            return $this->returnData();
        }

        $audit_where['where'] = 'galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id: AND is_delete = :is_delete:';
        $audit_where['value']['galaxy_wechat_integral_order_id'] = $id;
        $audit_where['value']['is_delete'] = 0;
        $params['audit_name'] = isset($userinfo['username']) ? $userinfo['username'] : '';
        $params['galaxy_admin_id'] = isset($userinfo['id']) ? $userinfo['id'] : 0;
        $params['update_time'] = date('Y-m-d H:i:s',$time);

        $galaxyWechatAuditMessageModel->saveData($audit_where,$params);
        if(!$galaxyWechatAuditMessageModel->getErrorResult()){
            $this->msg = "核销信息编辑成功";
            $this->code = 200;
            $this->data = true;
        }

        return $this->returnData();
    }

    /**
     * 核销详情
     * @return mixed
     */
    public function getDataService($id){
        $galaxyWechatAuditMessageModel = new GalaxyWechatAuditMessage();

        $where['where'] = 'galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id: AND is_delete = :is_delete:';
        $where['value']['galaxy_wechat_integral_order_id'] = $id;
        $where['value']['is_delete'] = 0;

        $galaxyWechatAuditMessageModel->getFindOne('', $where);
        $ret = $galaxyWechatAuditMessageModel->getSucceedResult(1);
        if(!$ret){
            $this->msg = "该核销信息不存在";
            return $this->returnData();
        }

        $data = $ret[0];
        $data['id'] = (int)$data['id'];
        $data['galaxy_admin_id'] = (int)$data['galaxy_admin_id'];
        $data['galaxy_wechat_integral_order_id'] = (int)$data['galaxy_wechat_integral_order_id'];
        $data['is_delete'] = (int)$data['is_delete'];

        $this->msg = "核销详情";
        $this->code = 200;
        $this->data = $data;

        return $this->returnData();
    }

}