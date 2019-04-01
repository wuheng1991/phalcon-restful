<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/18
 * Time: 15:52
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Backend\Services\GalaxyWechatGatherMessageServer;


class GatherMessageController extends ControllerBase
{
    public function onConstruct()
    {
        $this->GalaxyWechatGatherMessageServer = new GalaxyWechatGatherMessageServer();
    }

    /**
     * Method Http accept: get
     * @return json
     * 订单-收货详情
     */
    public function getDetailAction($id){
        if(!empty($id)){
            $ret = $this->GalaxyWechatGatherMessageServer->getDataService($id);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: put
     * @return json
     * 订单-收货编辑
     */
    public function saveAction($id){
        if(!empty($id)){
            $params = $this->request->getPut();
            $ret = $this->GalaxyWechatGatherMessageServer->saveDataService($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }
}