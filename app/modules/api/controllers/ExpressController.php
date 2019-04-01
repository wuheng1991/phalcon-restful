<?php
namespace Api\Modules\Api\Controllers;

use Api\Services\GalaxyWechatDeliverMessageServer;
use express\express;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/15
 * Time: 14:19
 */
class ExpressController extends ControllerBase{

    public function onConstruct(){
        $this->GalaxyWechatDeliverMessage = new GalaxyWechatDeliverMessageServer();
    }

    /**
     * 快递查询
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
        $response = $this->GalaxyWechatDeliverMessage->getExpress($oid);
        return $this->response->setJsonContent($response);
    }
    
}