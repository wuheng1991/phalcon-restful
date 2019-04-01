<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/24
 * Time: 14:38
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Backend\Services\GalaxyWechatClassifyServer;


class IntegralClassifyController extends ControllerBase
{
    public function onConstruct()
    {
        #parent::onConstruct();//继承父类构造

        $this->galaxyWechatClassifyServer = new GalaxyWechatClassifyServer();
    }

    /**
     * Method Http accept: get
     * @return data
     * 礼品分类列表
     */
    public function searchAction()
    {
        $params = $this->request->get();
        $ret = $this->galaxyWechatClassifyServer->searchDataService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
    * Method Http accept: get
    * @return json
    * 礼品分类详情
    */
    public function getAction($id){
        if(!empty($id)){
            $ret = $this->galaxyWechatClassifyServer->getDataService($id);
            return $this->response->setJsonContent($ret);
        }
    }


    /**
     * Method Http accept: post
     * @return json
     * 礼品分类创建
     */
    public function createAction(){
        $params = $this->request->getPost();
        $ret = $this->galaxyWechatClassifyServer->addDataService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: put
     * @return json
     * 礼品分类编辑
     */
    public function saveAction($id){
        if(!empty($id)){
            $params = $this->request->getPut();
            $ret = $this->galaxyWechatClassifyServer->saveDataService($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: put
     * @return json
     * 礼品分类停用/启用
     */
    public function stateAction($id){
        if(!empty($id)){
            $params = $this->request->getPut();
            $ret = $this->galaxyWechatClassifyServer->stateDataService($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: put
     * @return json
     * 礼品分类移动
     */
    public function moveAction($id){
        if(!empty($id)){
            $params = $this->request->getPut();
            $ret = $this->galaxyWechatClassifyServer->moveDataService($id,$params);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: get
     * @return json
     * 礼品分类移动-礼品分类列表
     */
    public function moveClassifyAction(){
        $ret = $this->galaxyWechatClassifyServer->moveClassifyDataService();
        return $this->response->setJsonContent($ret);
    }

}