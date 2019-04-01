<?php

namespace Api\Modules\Api\Controllers;

use Api\Services\GalaxyOrderServer;

class OrderController extends ControllerBase
{
    public function onConstruct(){
        $this->GalaxyOrderServer = new GalaxyOrderServer();
    }

    /**
     * 创建订单
     * @return mixed
     */
    public function createAction(){
        $parmas = $this->request->getPost();
        $response = array(
            "code"=>0,
            "data"=>'',
            "msg"=>"参数错误",
        );
        switch ($_POST["goods_type"]){
            case "虚拟礼品":
                $tmp_arr = array(
                    "goodsid","num"
                );
                break;
            case "实体礼品":
                $tmp_arr = array(
                    "detailed_address","detailed_name","detailed_phone","goodsid","num"
                );
                break;
            default:
                return $this->response->setJsonContent($response);
        }
        foreach($tmp_arr as $k=>$v){
            if(empty($parmas[$v])){
                return $this->response->setJsonContent($response);
            }
        }
        $response = $this->GalaxyOrderServer->createOrder($parmas,$this);
        return $this->response->setJsonContent($response);
    }


    /**
     * 订单兑换
     * @param $id
     * @return mixed
     */
    public function saveAction($id){
        if(empty($id)){
            $response = array(
                "code"=>0,
                "data"=>'',
                "msg"=>"参数错误",
            );
            return $this->response->setJsonContent($response);
        }
        $response = $this->GalaxyOrderServer->exchange($id);
        return $this->response->setJsonContent($response);
    }

    /**
     * 查询客户全部订单
     * @return mixed
     */
    public function getAllAction(){
        $parmas = $this->request->get();
//        if(empty($parmas['cid'])){
//            $response = array(
//                "code"=>0,
//                "data"=>'',
//                "msg"=>"参数错误",
//            );
//            return $this->response->setJsonContent($response);
//        }
        $parmas["cid"] = $this->id;
        $response = $this->GalaxyOrderServer->getAllOrder($parmas);
        return $this->response->setJsonContent($response);
    }

    /**
     * 查询订单详情
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
        $response = $this->GalaxyOrderServer->getOrder($oid);
        return $this->response->setJsonContent($response);
    }
}

