<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/11
 * Time: 10:27
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Backend\Services\GalaxyWechatDeliverMessageServer;


class DeliverMessageController extends ControllerBase
{
    public function onConstruct()
    {
        $this->galaxyWechatDeliverMessageServer = new GalaxyWechatDeliverMessageServer();
    }

    /**
     * Method Http accept: post
     * @return json
     * 订单-发货创建
     */
    public function createAction($id){
        if(!empty($id)){
            $params = $this->request->getPost();
            $ret = $this->galaxyWechatDeliverMessageServer->addDataService($id, $params, $this->userinfo);
            return $this->response->setJsonContent($ret);
        }

    }

    /**
     * Method Http accept: put
     * @return json
     * 订单-发货编辑
     */
    public function saveAction($id){
        if(!empty($id)){
            $params = $this->request->getPut();
            $ret = $this->galaxyWechatDeliverMessageServer->saveDataService($id, $params, $this->userinfo);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: get
     * @return json
     * 订单-发货详情
     */
    public function getAction($id){
        if(!empty($id)){
            $ret = $this->galaxyWechatDeliverMessageServer->getDataService($id);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: get
     * @return json
     * 快递公司列表
     */
    public function expressCompanyAction(){
        $ret = $this->galaxyWechatDeliverMessageServer->getExpressCompanyDataService();
        return $this->response->setJsonContent($ret);
    }

}