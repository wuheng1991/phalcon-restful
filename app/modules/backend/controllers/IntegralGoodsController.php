<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 16:10
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Backend\Services\GalaxyWechatIntegralGoodsServer;


class IntegralGoodsController extends ControllerBase
{
    public function onConstruct()
    {
        #parent::onConstruct();//继承父类构造

        $this->galaxyWechatIntegralGoodsServer = new GalaxyWechatIntegralGoodsServer();
    }

    /**
     * Method Http accept: get
     * @return data
     * 礼品列表
     */
    public function searchAction()
    {
        $params = $this->request->get();
        $ret = $this->galaxyWechatIntegralGoodsServer->searchDataService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: get
     * @return json
     * 礼品详情
     */
    public function getAction($id){
        if(!empty($id)){
            $ret = $this->galaxyWechatIntegralGoodsServer->getDataService($id);
            return $this->response->setJsonContent($ret);
        }
    }


    /**
     * Method Http accept: post
     * @return json
     * 礼品创建
     */
    public function createAction(){
        $params = $this->request->getPost();
        $ret = $this->galaxyWechatIntegralGoodsServer->addDataService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: put
     * @return json
     * 礼品编辑
     */
    public function saveAction($id){
        if(!empty($id)){
            $params = $this->request->getPut();
            $ret = $this->galaxyWechatIntegralGoodsServer->saveDataService($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: put
     * @return json
     * 礼品上架/下架
     */
    public function stateAction($id){
        if(!empty($id)){
            $params = $this->request->getPut();
            $ret = $this->galaxyWechatIntegralGoodsServer->stateDataService($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: put
     * @return json
     * 礼品排序
     */
    public function sortAction($id){
        if(!empty($id)){
            $params = $this->request->getPut();
            $ret = $this->galaxyWechatIntegralGoodsServer->sortDataService($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: put
     * @return json
     * 获取排序第一与排序最后的数据
     */
    public function getFistAndLastSortAction(){
        $params = $this->request->get();
        $ret = $this->galaxyWechatIntegralGoodsServer->getFistAndLastSortDataService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: get
     * @return data
     * 积分礼品数量统计
     * 0-下架，1-上架
     */
    public function stateCountAction(){
        $params = $this->request->get();
        $ret = $this->galaxyWechatIntegralGoodsServer->stateCountDataService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: get
     * @return json
     * 礼品分类列表
     */
    public function classifyAction(){
        $ret = $this->galaxyWechatIntegralGoodsServer->classifyDataService();
        return $this->response->setJsonContent($ret);
    }
}