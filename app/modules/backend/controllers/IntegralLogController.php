<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/27
 * Time: 12:55
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Backend\Services\GalaxyWechatIntegralLogServer;


class IntegralLogController extends ControllerBase
{
    public function onConstruct()
    {
        #parent::onConstruct();//继承父类构造

        $this->galaxyWechatIntegralLogServer = new GalaxyWechatIntegralLogServer();
    }

    /**
     * Method Http accept: post
     * @return json
     * 可用积分调整
     */
    public function createAction($id){
        if(!empty($id)){
            $userinfo = $this->userinfo;
            $params = $this->request->getPost();
            $ret = $this->galaxyWechatIntegralLogServer->addDataService($id, $params, $userinfo);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: get
     * @return data
     * 客户积分记录列表
     */
    public function searchAction($id=0)
    {
        $params = $this->request->get();
        $ret = $this->galaxyWechatIntegralLogServer->searchDataService($id, $params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: get
     * @return data
     * 单个客户可用积分以及已兑换积分
     */
    public function availableIntegralAction($id){
        if(!empty($id)){
            $params = $this->request->get();
            $ret = $this->galaxyWechatIntegralLogServer->availableIntegralDataService($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: get
     * @return data
     * 积分记录下载
     */
    public function excelAction(){
        $params = $this->request->get();
        $ret = $this->galaxyWechatIntegralLogServer->excelDataService($params);
        return $this->response->setJsonContent($ret);
    }

}