<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/29
 * Time: 18:39
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Backend\Services\GalaxyWechatAuditMessageServer;


class AuditMessageController extends ControllerBase
{
    public function onConstruct()
    {
        $this->galaxyWechatAuditMessageServer = new GalaxyWechatAuditMessageServer();
    }

    /**
     * Method Http accept: post
     * @return json
     * 订单-核销创建
     */
    public function createAction($id){
        if(!empty($id)){
            $params = $this->request->getPost();
            $ret = $this->galaxyWechatAuditMessageServer->addDataService($id, $params, $this->userinfo);
            return $this->response->setJsonContent($ret);
        }

    }

    /**
     * Method Http accept: put
     * @return json
     * 订单-核销编辑
     */
    public function saveAction($id){
        if(!empty($id)){
            $params = $this->request->getPut();
            $ret = $this->galaxyWechatAuditMessageServer->saveDataService($id, $params, $this->userinfo);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: get
     * @return json
     * 订单-核销详情
     */
    public function getAction($id){
        if(!empty($id)){
            $ret = $this->galaxyWechatAuditMessageServer->getDataService($id);
            return $this->response->setJsonContent($ret);
        }
    }


}