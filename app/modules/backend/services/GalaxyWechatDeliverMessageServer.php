<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/11
 * Time: 10:32
 */
namespace Backend\Services;
use Api\Models\GalaxyWechatDeliverMessage;
use Api\Models\GalaxyExpressConfig;
use Api\Models\GalaxyWechatIntegralOrder;
use Api\Models\GalaxyWechatClient;
use Api\Models\GalaxyWechatGatherMessage;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileLogger;

class GalaxyWechatDeliverMessageServer extends BaseServer
{
    /**
     * 发货增添
     * @return mixed
     */
    public function addDataService($id, $params ,$userinfo){
        $galaxyWechatDeliverMessageModel = new GalaxyWechatDeliverMessage();
        $galaxyWechatIntegralOrderModel = new GalaxyWechatIntegralOrder();
        $galaxyWechatClientModel = new GalaxyWechatClient();
        $galaxyExpressConfigModel = new GalaxyExpressConfig();
        $galaxyWechatGatherMessageModel = new GalaxyWechatGatherMessage();
        $time = time();

        if(isset($params['express_company_id']) && empty($params['express_company_id'])){
            $this->msg = "快递公司必须选择";
            return $this->returnData();
        }

        if(isset($params['express_number']) && empty($params['express_number'])){
            $this->msg = "快递单号不能为空";
            return $this->returnData();
        }

        if(isset($params['deliver_message']) && empty($params['deliver_message'])){
            $this->msg = "发货内容不能为空";
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

        #判断快递公司是否存在
        if(isset($params['express_company_id'])){
            $express_config_where['where'] = 'id = :id:';
            $express_config_where['value']['id'] = (int)$params['express_company_id'];

            $galaxyExpressConfigModel->getFindOne('experss_company, express_company_code', $express_config_where);
            $expressConfigRet = $galaxyExpressConfigModel->getSucceedResult(1);
            if(!$expressConfigRet){
                $this->msg = "该快递公司不存在";
                return $this->returnData();
            }

            $params['express_company'] = $expressConfigRet[0]['experss_company'];
            $params['express_company_spell'] = $expressConfigRet[0]['express_company_code'];
        }

        #判断是否已经发货
        $deliver_message_where['where'] = "galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id:";
        $deliver_message_where['value']['galaxy_wechat_integral_order_id'] = $id;

        $galaxyWechatDeliverMessageModel->getFindOne('id', $deliver_message_where);
        $deliverMessageRet = $galaxyWechatDeliverMessageModel->getSucceedResult(1);
        if($deliverMessageRet){
            $this->msg = "该订单已创建发货";
            return $this->returnData();
        }

        #订单发货信息
        $params['galaxy_admin_id'] = isset($userinfo['id']) ? $userinfo['id'] : 0;
        $params['galaxy_wechat_integral_order_id'] = $id;

        $ret = $galaxyWechatDeliverMessageModel->createData($params);
        if($ret) {
            #若订单状态为“已兑换”-2，修改状态为“已发货”-3
            if($integralOrderRet[0]['order_type'] == '2'){
                $order_params['order_type'] = 3;
            }

            $order_params['update_time'] = date('Y-m-d H:i:s',$time);
            $galaxyWechatIntegralOrderModel->saveData($order_where,$order_params);
            if($galaxyWechatIntegralOrderModel->getErrorResult()){
                $this->msg = "订单状态修改失败";
                return $this->returnData();
            }

            #----------推送微信模板消息开始---------------

            #获取客户的openid
            $client_where['where'] = 'id = :id: AND is_deleted = :is_deleted:';
            $client_where['value']['id'] = (int)$integralOrderRet[0]['galaxy_wechat_client_id'];
            $client_where['value']['is_deleted'] = 0;
            $clientRet = $galaxyWechatClientModel->findone($client_where,$field="id, openid");
            if(!$clientRet){
                $this->msg = "收货人不存在";
                return $this->returnData();
            }
            $openid = $clientRet->openid;
            #发货时间
            $deliver_date = date("Y-m-d H:i", $time);
            #收货地址
            $gather_message_where['where'] = 'galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id:';
            $gather_message_where['value']['galaxy_wechat_integral_order_id'] = $id;
            $galaxyWechatGatherMessageModel->getFindOne('detailed_address', $gather_message_where);
            $gatherMessageRet = $galaxyWechatGatherMessageModel->getSucceedResult(1);
            $detailed_address = !empty($gatherMessageRet) ? $gatherMessageRet[0]['detailed_address'] : '';
            #跳转的url
            $template_url = '';
            #判断订单（0:实体/1:虚拟）
            if($integralOrderRet[0]['goods_type'] == 0){
                # 订单发货通知
                $template_id = 'order_send_message';
                $template_data = [
                    'first' => '您好,您兑换的礼品已经发货！',
                    'keyword1' => isset($integralOrderRet[0]['goods_name']) ? $integralOrderRet[0]['goods_name'] : '',#兑换礼品
                    'keyword2' => $deliver_date, #发货时间
                    'keyword3' => isset($expressConfigRet[0]['experss_company']) ? $expressConfigRet[0]['experss_company'] : '', #物流公司
                    'keyword4' => isset($params['express_number']) ? $params['express_number'] : '',
                    'keyword5' => $detailed_address, #快递单号
                    'remark' => '快递将很快到达您的手中，请注意查收哦！',
                ];
            }else{
                # 积分兑换通知
                $template_id = 'integral_exchange';
                $template_data = [
                    'first' => '您好,您兑换的礼品已经发货！',
                    'keyword1' => isset($integralOrderRet[0]['orderid']) ? $integralOrderRet[0]['orderid'] : '', #订单号
                    'keyword2' => isset($integralOrderRet[0]['goods_name']) ? $integralOrderRet[0]['goods_name'] : '', #兑换礼品
                    'keyword3' => isset($params['deliver_message']) ? $params['deliver_message'] : '', #当前内容
                    'keyword4' => $deliver_date, #发货时间
                    'remark' => '感谢您的使用，如有疑问请拨打400-2858-465',
                ];
            }

            #微信消息数据处理
            $message = $this->getWechatPutMessage($openid, $template_id, $template_data, $template_url);
            #推送消息
            $messageRet = di("wechat")->pushMessage($message);

            $logObj = $this->getLogMessage();
            $goods_type_value = ($integralOrderRet[0]['goods_type'] == 0) ? '实体' : '虚拟';
            if(isset($messageRet['errcode']) && $messageRet['errcode'] == 0){
                $logObj->log(\Phalcon\Logger::INFO, '创建发货-微信消息推送成功。订单id='.$id.'; 类型='.$goods_type_value);
            }else{
                $logObj->log(\Phalcon\Logger::ERROR, '创建发货-微信消息推送失败。订单id='.$id.'; 类型='.$goods_type_value);
            }
            #----------推送微信模板消息结束----------------

            $this->msg = "订单发货增添成功";
            $this->code = 200;
            $this->data = true;
        }

        return $this->returnData();
    }


    /**
     * 发货编辑
     * @return mixed
     */
    public function saveDataService($id, $params, $userinfo){
        $galaxyWechatDeliverMessageModel = new GalaxyWechatDeliverMessage();
        $galaxyWechatIntegralOrderModel = new GalaxyWechatIntegralOrder();
        $galaxyWechatClientModel = new GalaxyWechatClient();
        $galaxyExpressConfigModel = new GalaxyExpressConfig();
        $galaxyWechatGatherMessageModel = new GalaxyWechatGatherMessage();
        $time = time();

        if(isset($params['express_company_id']) && empty($params['express_company_id'])){
            $this->msg = "快递公司必须选择";
            return $this->returnData();
        }

        if(isset($params['express_number']) && empty($params['express_number'])){
            $this->msg = "快递单号不能为空";
            return $this->returnData();
        }

        if(isset($params['deliver_message']) && empty($params['deliver_message'])){
            $this->msg = "发货内容不能为空";
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

        #判断快递公司是否存在
        if(isset($params['express_company_id'])){
            $express_config_where['where'] = 'id = :id:';
            $express_config_where['value']['id'] = (int)$params['express_company_id'];

            $galaxyExpressConfigModel->getFindOne('experss_company, express_company_code', $express_config_where);
            $expressConfigRet = $galaxyExpressConfigModel->getSucceedResult(1);
            if(!$expressConfigRet){
                $this->msg = "该快递公司不存在";
                return $this->returnData();
            }

            $params['express_company'] = $expressConfigRet[0]['experss_company'];
            $params['express_company_spell'] = $expressConfigRet[0]['express_company_code'];
        }

        //更新时间
        $params['update_time'] = date('Y-m-d H:i:s',$time);
        $params['galaxy_admin_id'] = isset($userinfo['id']) ? $userinfo['id'] : 0;
        $deliver_where['where'] = 'galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id:';
        $deliver_where['value']['galaxy_wechat_integral_order_id'] = $id;

        $galaxyWechatDeliverMessageModel->saveData($deliver_where,$params);
        if(!$galaxyWechatDeliverMessageModel->getErrorResult()){
            #----------推送微信模板消息开始---------------

            #获取客户的openid
            $client_where['where'] = 'id = :id: AND is_deleted = :is_deleted:';
            $client_where['value']['id'] = (int)$integralOrderRet[0]['galaxy_wechat_client_id'];
            $client_where['value']['is_deleted'] = 0;
            $clientRet = $galaxyWechatClientModel->findone($client_where,$field="id, openid");
            if(!$clientRet){
                $this->msg = "收货人不存在";
                return $this->returnData();
            }
            $openid = $clientRet->openid;
            #发货时间
            $deliver_date = date('Y-m-d H:i',$time);
            #收货地址
            $gather_message_where['where'] = 'galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id:';
            $gather_message_where['value']['galaxy_wechat_integral_order_id'] = $id;
            $galaxyWechatGatherMessageModel->getFindOne('detailed_address', $gather_message_where);
            $gatherMessageRet = $galaxyWechatGatherMessageModel->getSucceedResult(1);
            $detailed_address = !empty($gatherMessageRet) ? $gatherMessageRet[0]['detailed_address'] : '';
            #跳转的url
            $template_url = '';
            #判断订单（0:实体/1:虚拟）
            if($integralOrderRet[0]['goods_type'] == 0){
                # 订单发货通知
                $template_id = 'order_send_message';
                $template_data = [
                    'first' => '您好，您兑换的礼品发货信息已更新！',
                    'keyword1' => isset($integralOrderRet[0]['goods_name']) ? $integralOrderRet[0]['goods_name'] : '', #兑换礼品
                    'keyword2' => $deliver_date, #发货时间
                    'keyword3' => isset($expressConfigRet[0]['experss_company']) ? $expressConfigRet[0]['experss_company'] : '', #物流公司
                    'keyword4' => isset($params['express_number']) ? $params['express_number'] : '',
                    'keyword5' => $detailed_address, #快递单号
                    'remark' => '快递将很快到达您的手中，请注意查收哦！',
                ];
            }else{
                # 积分兑换通知
                $template_id = 'integral_exchange';
                $template_data = [
                    'first' => '您好，您兑换的礼品发货信息已更新！',
                    'keyword1' => isset($integralOrderRet[0]['orderid']) ? $integralOrderRet[0]['orderid'] : '', #订单号
                    'keyword2' => isset($integralOrderRet[0]['goods_name']) ? $integralOrderRet[0]['goods_name'] : '', #兑换礼品
                    'keyword3' => isset($params['deliver_message']) ? $params['deliver_message'] : '', #当前内容
                    'keyword4' => $deliver_date, #发货时间
                    'remark' => '感谢您的使用，如有疑问请拨打400-2858-465',
                ];
            }

            #微信消息数据处理
            $message = $this->getWechatPutMessage($openid, $template_id, $template_data, $template_url);
            #推送消息
            $messageRet = di("wechat")->pushMessage($message);

            $logObj = $this->getLogMessage();
            $goods_type_value = ($integralOrderRet[0]['goods_type'] == 0) ? '实体' : '虚拟';
            if(isset($messageRet['errcode']) && $messageRet['errcode'] == 0){
                $logObj->log(\Phalcon\Logger::INFO, '编辑发货-微信消息推送成功。订单id='.$id.';类型='.$goods_type_value);
            }else{
                $logObj->log(\Phalcon\Logger::ERROR, '编辑发货-微信消息推送失败。订单id='.$id.';类型='.$goods_type_value);
            }
            #----------推送微信模板消息结束----------------
            $this->msg = "订单发货信息编辑成功";
            $this->code = 200;
            $this->data = true;
        }

        return $this->returnData();

    }


    /**
     * 发货详情
     * @return mixed
     */
    public function getDataService($id){
        $galaxyWechatDeliverMessageModel = new GalaxyWechatDeliverMessage();

        $where['where'] = 'galaxy_wechat_integral_order_id = :galaxy_wechat_integral_order_id:';
        $where['value']['galaxy_wechat_integral_order_id'] = $id;


        $galaxyWechatDeliverMessageModel->getFindOne('', $where);
        $ret = $galaxyWechatDeliverMessageModel->getSucceedResult(1);
        if(!$ret){
            $this->msg = "该订单发货信息不存在";
            return $this->returnData();
        }

        $data = $ret[0];
        $data['id'] = (int)$data['id'];
        $data['galaxy_admin_id'] = (int)$data['galaxy_admin_id'];
        $data['galaxy_wechat_integral_order_id'] = (int)$data['galaxy_wechat_integral_order_id'];
        $data['express_status'] = (int)$data['express_status'];
        $data['express_company_id'] = (int)$data['express_company_id'];


        $this->msg = "订单发货详情";
        $this->code = 200;
        $this->data = $data;

        return $this->returnData();
    }

    /**
     * 快递公司列表
     * @return mixed
     */
    public function getExpressCompanyDataService(){
        $galaxyExpressConfigModel = new GalaxyExpressConfig();
        $where['where']= '';
        $where['value']= [];

        $galaxyExpressConfigModel->getFindAll($select = '',$where,$toLimit = array(),$toBy = array());
        $data = $galaxyExpressConfigModel->getSucceedResult(1);

        if($data){
            foreach($data as $k => $v){
                $data[$k]['id'] = (int)$v['id'];
                $data[$k]['internation'] = (int)$v['internation'];
            }
        }

        $this->code = 200;
        $this->msg = '快递公司列表';
        $this->data = $data;

        return $this->returnData();
    }

    /**
     * 消息推送日志
     * @return mixed
     */
    public function getLogMessage(){
        date_default_timezone_set('Asia/Shanghai');
        $log = APP_PATH.'/logs/wechat_message.log';
        if(!file_exists($log)){
            fopen($log, "a+");
            //mkdir($task_log,0777, true);
        }
        $loggerObj = new FileLogger($log);
        //$formatter = new LineFormatter("[%date% - %message% - %type%]");
        //$this->logger->setFormatter($formatter);
        return $loggerObj;
    }

}