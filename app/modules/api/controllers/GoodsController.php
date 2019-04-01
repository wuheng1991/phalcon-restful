<?php

namespace Api\Modules\Api\Controllers;

use Api\Services\GalaxyGoodsServer;
use Api\Services\GalaxyWechatClientServer;

class GoodsController extends ControllerBase
{
    public function onConstruct(){
        $this->GalaxyGoodsServer = new GalaxyGoodsServer();
    }

    /**
     * 公众号-商品列表
     * @return mixed
     */
    public function goodsListAction(){
        $params = $this->request->get();
        $response = $this->GalaxyGoodsServer->goodsList($params);
        return  $this->response->setJsonContent($response);
    }

    /**
     * 公众号-商品详情
     * @param $id
     * @return mixed
     */
    public function goodsDetailAction($id){
        if(empty((int)$id) || !is_numeric((int)$id)){
            $response = array(
                "code"=>0,
                "data"=>'',
                "msg"=>"参数不合法",
            );
            return $this->response->setJsonContent($response);
        }
        $response = $this->GalaxyGoodsServer->goodsDetail($id);
        return  $this->response->setJsonContent($response);
    }

    /**
     * 立即兑换验证
     * @return mixed
     */
    public function  exchangeAction(){
        $params = $this->request->get();
        if(empty((int)$params["goodsid"]) || empty((int)$params["num"])){
            $response = array(
                "code"=>0,
                "data"=>'',
                "msg"=>"参数不合法",
            );
            return $this->response->setJsonContent($response);
        }
        $response = $this->GalaxyGoodsServer->exchange($params,$this->id);
        return  $this->response->setJsonContent($response);
    }

    public function setAction(){
//        $this->redis->hIncrBy("goods:num",1,-6);
        $a = $this->GalaxyGoodsServer->setGoodsNum(1,5);
        pr($a);
    }
    //兑换


}

