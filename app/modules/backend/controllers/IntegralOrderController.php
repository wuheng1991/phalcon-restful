<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/27
 * Time: 16:38
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Backend\Services\GalaxyWechatIntegralOrderServer;


class IntegralOrderController extends ControllerBase
{
    public function onConstruct()
    {
        #parent::onConstruct();//继承父类构造
        $this->GalaxyWechatIntegralOrderServer = new GalaxyWechatIntegralOrderServer();
    }

    /**
     * Method Http accept: get
     * @return data
     * 订单列表
     */
    public function searchAction()
    {
        $params = $this->request->get();
        $ret = $this->GalaxyWechatIntegralOrderServer->searchDataService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: get
     * @return data
     * 订单详情
     */
    public function getAction($id)
    {
        $params = $this->request->get();
        $ret = $this->GalaxyWechatIntegralOrderServer->getDataService($id, $params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: get
     * @return data
     * 订单下载
     */
    public function excelAction(){
        $params = $this->request->get();
        $ret = $this->GalaxyWechatIntegralOrderServer->excelDataService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: get
     * @return data
     * 订单状态对应的数量统计
     * 1-待兑换，2-已兑换，4-已完成 100-已取消
     */
    public function opderTypeCountAction(){
        $params = $this->request->get();
        $ret = $this->GalaxyWechatIntegralOrderServer->opderTypeCountDataService($params);
        return $this->response->setJsonContent($ret);
    }

}