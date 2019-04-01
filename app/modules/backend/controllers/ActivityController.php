<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 11:12
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Api\Models\GalaxyWechatActivity;
use Phalcon\DI;
use Phalcon\Mvc\Dispatcher;
use Backend\Services\GalaxyWechatActivityServer;

class ActivityController extends ControllerBase
{

    public function onConstruct()
    {
        #parent::onConstruct();//继承父类构造
        $this->galaxyWechatActivityModel = new GalaxyWechatActivity();
        $this->galaxyWechatActivityServer = new GalaxyWechatActivityServer();
    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function indexAction(){
        //$galaxyWechatActivityServer = new GalaxyWechatActivityServer();
    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function getAction($id){
        if($id){
            $ret = $this->galaxyWechatActivityModel->getData($id);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: get
     * @return data
     */
    public function searchAction()
    {
        $params = $this->request->get();
        $ret = $this->galaxyWechatActivityModel->searchData($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: post
     * @return json
     */
    public function createAction(){
        $params = $this->request->getPost();
        $ret = $this->galaxyWechatActivityServer->addDataService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: put
     * @return json
     */
    public function saveAction($id){
        if(!empty($id)){
            $params = $this->request->getPut();
            $ret = $this->galaxyWechatActivityServer->saveDataService($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: delete
     * @return json
     */
    public function deleteAction($id)
    {
        if(!empty($id)){
            $ret = $this->galaxyWechatActivityModel->deleteData($id);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
 * Method Http accept: get
 * @return json
 */
    public function settingAction(){
        $params = $this->request->get();
        $ret = $this->galaxyWechatActivityModel->settingData($params);
        return $this->response->setJsonContent($ret);
    }

    //发送邮件
    public function getSendQrAction()
    {
        $params = $this->request->get();
        $ret = $this->galaxyWechatActivityServer->sendActivityQr($params);
        return $this->response->setJsonContent($ret);
    }

}