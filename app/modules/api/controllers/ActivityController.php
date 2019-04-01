<?php

namespace Api\Modules\Api\Controllers;

use Api\Services\ActivityServer;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Controller;

class ActivityController extends ControllerBase
{
    public $code;
    public $data;
    public $server_obj;

    public function onConstruct(){
        $this->url = $this->di->get('config')->apiUrl;
        $this->activityServer = new ActivityServer();
    }

    //获取活动列表
    public function getListAction(){
        $params = $this->request->get();
        $data = $this->activityServer->getActivityList($params,$this);
        return $this->response->setJsonContent($data);
    }

    //获取活动详情
    public function getDetailsAction($id,$owner=0){
        $data = $this->activityServer->getActivityDetails($id,$owner,$this);
        return $this->response->setJsonContent($data);
    }

    //新增方法封装
    public function saveSubscribeAction($id){
        $data = $this->request->getPut();
        if(empty($data['phone']) || empty($data['username'])){
             $response = [
                "code" => 0,
                "data" => "",
                "msg" => "手机号码和姓名不能为空"
            ];
            return $this->response->setJsonContent($response);
        }

        if(empty($id)){
             $response = [
                "code" => 0,
                "data" => "",
                "msg" => "活动不存在"
            ];
            return $this->response->setJsonContent($response);
        }
        $data['id'] = $id;
        $ret = $this->activityServer->addSubscribeInfo($data,$this);
        return $this->response->setJsonContent($ret);

        // $sms_obj = $this->di->get("SmsHelper");
        // $code_res = $sms_obj->validateCode($data['phone'],$code);

        // //验证不通过
        // if($code_res['code'] != 200){
        //     $response = [
        //         "code" => 0,
        //         "data" => "",
        //         "msg" => $code_res['msg'];
        //     ];
        //     return $this->response->setJsonContent($response);
        // }
        // $ret = $this->activityServer->addSubscribeInfo($data,$this);
    }

    //签到接口页面
    public function getInAction($id){
        $data = $this->activityServer->getSignIn($id,$this);
        return $this->response->setJsonContent($data);
    }

    //签退接口页面
    public function getOutAction($id){
        $data = $this->activityServer->getSignOut($id,$this);
        return $this->response->setJsonContent($data);
    }
    
    //签到操作
    public function saveInAction($id){
        $data = $this->activityServer->saveSignIn($id,$this);
        return $this->response->setJsonContent($data);
    }

    //签退操作
    public function saveOutAction($id){
        $data = $this->activityServer->saveSignOut($id,$this);
        return $this->response->setJsonContent($data);
    }

    /**
     * 活动短信发送接口
     * @return mixed
     */
    public function sendmsgAction($mobile)
    {
//        if($mobile == '13480177643'){
//            $response = [
//                "code" => 200,
//                "data" => "",
//                "msg" =>"发送成功"
//            ];
//        }else{
            $send_res = $this->SmsHelper->send($mobile,33873);
            $response = [
                "code" => $send_res[0],
                "data" => "",
                "msg" =>$send_res[1]
            ];
//        }
        return $this->response->setJsonContent($response);
    }

}

