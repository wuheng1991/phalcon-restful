<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/27
 * Time: 18:55
 */
namespace Backend\Services;

use Api\Models\GalaxyWechatIntegralSetting;
use Api\Models\GalaxyWechatIntegralStats;

class GalaxyWechatIntegralSettingServer extends BaseServer
{
    public function getDataService($params){
        $galaxyWechatIntegralSettingModel = new GalaxyWechatIntegralSetting();
        $where['where'] = '';
        $where['value'] = [];

        $galaxyWechatIntegralSettingModel->getFindAll($select = 'id, model, integral',$where,$toLimit = array(),$toBy = array());
        $ret = $galaxyWechatIntegralSettingModel->getSucceedResult(1);

        $data = [];
        if($ret){
            foreach($ret as $k=>$v){
                if($v['id'] == '1'){
                    $data['effective_customer_integral'] = (int)$v['integral'];
                }else if($v['id'] == '2'){
                    $data['success_signing_integral'] = (int)$v['integral'];
                }
            }
        }

        $this->msg = "积分配置数据";
        $this->code = 200;
        $this->data = $data;

        return $this->returnData();
    }
    /**
     * 积分配置设置
     * @return mixed
     */
    public function saveDataService($params){
        $galaxyWechatIntegralSettingModel = new GalaxyWechatIntegralSetting();
        $time = time();
        # 1-成功推荐1位有效客户所得积分 ; 2-推荐1位客户成功签约所得积分
        if(isset($params['effective_customer_integral']) && ($params['effective_customer_integral'] < 1 || $params['effective_customer_integral'] > 1000000)){
            $this->msg = "成功推荐1位有效客户所得积分不能为空或小于1且最大为1000000";
            return $this->returnData();
        }

        if(isset($params['effective_customer_integral']) && !preg_match("/^[1-9][0-9]*$/",$params['effective_customer_integral'])){
            $this->msg = "成功推荐1位有效客户所得积分必须为正整数";
            return $this->returnData();
        }

        if(isset($params['success_signing_integral']) && ($params['success_signing_integral'] < 1 || $params['success_signing_integral'] > 1000000)){
            $this->msg = "推荐1位客户成功签约所得积分不能为空或小于1且最大为1000000";
            return $this->returnData();
        }

        if(isset($params['success_signing_integral']) && !preg_match("/^[1-9][0-9]*$/",$params['success_signing_integral'])){
            $this->msg = "推荐1位客户成功签约所得积分必须为正整数";
            return $this->returnData();
        }

        $where['where'] = '';
        $where['value'] = [];

        $galaxyWechatIntegralSettingModel->getFindAll($select = 'id, integral',$where,$toLimit = array(),$toBy = array());
        $data = $galaxyWechatIntegralSettingModel->getSucceedResult(1);
        if($data){
            foreach($data as $k => $v){
                if($v['id']==1){
                    $where['where'] = 'id = :id:';
                    $where['value']['id'] = $v['id'];
                    $update['integral'] = isset($params['effective_customer_integral']) ? (int)$params['effective_customer_integral'] : $v['integral'];
                    $update['update_time'] = date("Y-m-d H:i:s", $time);
                    $galaxyWechatIntegralSettingModel->saveData($where, $update);
                }else if($v['id']==2){
                    $where['where'] = 'id = :id:';
                    $where['value']['id'] = $v['id'];
                    $update['integral'] = isset($params['success_signing_integral']) ? (int)$params['success_signing_integral'] : $v['integral'];
                    $update['update_time'] = date("Y-m-d H:i:s", $time);
                    $galaxyWechatIntegralSettingModel->saveData($where, $update);
                }
            }
        }

        if(!$galaxyWechatIntegralSettingModel->getErrorResult()){
            $this->msg = "积分配置设置成功";
            $this->code = 200;
            $this->data = true;
        }

        return $this->returnData();
    }
}