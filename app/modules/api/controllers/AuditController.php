<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 11:44
 */
namespace Api\Modules\Api\Controllers;
use Api\Services\GalaxyWechatAuditMessageServer;

class AuditController extends ControllerBase{
    public function onConstruct(){
        $this->GalaxyWechatAuditMessageServer = new GalaxyWechatAuditMessageServer();
    }

    /**
     * 获取核销凭证
     * @param $oid
     * @return mixed
     */
    public function getAction($oid){
        if(empty($oid)){
            $response = array(
                "code"=>0,
                "data"=>'',
                "msg"=>"参数错误",
            );
            return $this->response->setJsonContent($response);
        }
        $response = $this->GalaxyWechatAuditMessageServer->getAudit($oid);
        return $this->response->setJsonContent($response);
    }
}