<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 11:44
 */
namespace Api\Modules\Api\Controllers;

use Api\Services\GalaxyWechatIntegralLogServer;

class IntegralController extends ControllerBase{
    public function onConstruct(){
        $this->GalaxyWechatIntegralLogServer = new GalaxyWechatIntegralLogServer();
    }

    /**
     * 获取积分记录
     * @param $type
     * @return mixed
     */
    public function getIntegralLogListAction($type){
        if(empty($type)){
            $response = [
                "code"=>0,
                "data"=>'',
                "msg"=>"参数错误",
            ];
            return $this->response->setJsonContent($response);
        }
        $response = $this->GalaxyWechatIntegralLogServer->getIntegralList($type,$this->id);
        return $this->response->setJsonContent($response);
    }
}