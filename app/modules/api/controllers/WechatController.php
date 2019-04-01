<?php

namespace Api\Modules\Api\Controllers;

use Api\Services\GalaxyWechatClientServer;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Controller;

class WechatController extends Controller
{
    public $code;
    public $data;
    public $server_obj;

    public function onConstruct(){
//        parent::onConstruct();
        $this->GalaxyWechatClientServer = new GalaxyWechatClientServer();
    }

    public function indexAction()
    {
        echo 'api';exit;
    }

    /**
     * 微信code请求链接
     * @return mixed
     */
    public function getUserCodeUrlAction(){
        $url = $this->request->get("url");
        $urls = $this->wechat->getUserCode($url);
        $response =[
            "code"=>200,
            "data"=>$urls,
            "msg"=>"请求成功"
        ];
        return $this->response->setJsonContent($response);
    }

    /**
     * 从微信服务器获取微信用户信息并返回token
     * @param $code
     * @return mixed
     */
    public function getTokenBycodeAction($code){
        if(empty($code)){
            $response =[
                "code"=>0,
                "data"=>'',
                "msg"=>"参数错误"
            ];
            return $this->response->setJsonContent($response);
        }
        $this->code = $code;
        $response = $this->GalaxyWechatClientServer->getWechatToken($this);
        return $this->response->setJsonContent($response);
    }

    /**
     * 获取js-sdk配置
     * @param $url
     * @return mixed
     */
    public function getsignatureAction(){
        $url = $this->request->get('url');
        if(empty($url)){
            $response =[
                "code"=>0,
                "data"=>'',
                "msg"=>"参数错误"
            ];
            return $this->response->setJsonContent($response);
        }
//        $redis_signature = $this->modelsCache->get("signature:signature_config:");
//        if(!empty($redis_signature)){
//            $response =[
//                "code"=>200,
//                "data"=>json_decode($redis_signature,true),
//                "msg"=>"请求成功"
//            ];
//            return $this->response->setJsonContent($response);
//        }
        $response = $this->GalaxyWechatClientServer->getGrantConfig($url);
        return $this->response->setJsonContent($response);
    }

    public function getTokenAction($id){
        $response = $this->GalaxyWechatClientServer->getToken($id);
        return $this->response->setJsonContent($response);
    }
    
}

