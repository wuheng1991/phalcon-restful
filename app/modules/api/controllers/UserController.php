<?php

namespace Api\Modules\Api\Controllers;
use Api\models\GalaxyWechatClient;
use Api\Services\GalaxyUserServer;
class UserController extends ControllerBase
{
    public $userinfo;
    public function onConstruct(){
        $this->GalaxyUserServer = new GalaxyUserServer();
    }
    /**
     * 会员注册短信发送接口
     * @return mixed
     */
    public function sendmsgAction($mobile)
    {
        $client_obj = new GalaxyWechatClient();
        $exist_where["where"] = "phone = :phone: and vip = :vip:";
        $exist_where["value"]["phone"] = $mobile;
        $exist_where["value"]["vip"] = 1;
        $exist_res = $client_obj->findone($exist_where);
        if($exist_res){
            $response = [
                "code"=>0,
                "data"=>'',
                "msg"=>"手机号已注册会员",
            ];
            return $this->response->setJsonContent($response);
        }
        $send_res = $this->SmsHelper->send($mobile);
        $response = [
            "code" => $send_res[0],
            "data" => "",
            "msg" =>$send_res[1]
        ];
        return $this->response->setJsonContent($response);
    }

    /**
     * 获取用户信息
     * @return mixed
     */
    public function getUserinfoAction(){
        if(empty($this->id) && empty($this->openid)){
            $response = [
                "code" => 0,
                "data" => "",
                "msg" =>"用户未登录"
            ];
            return $this->response->setJsonContent($response);
        }
        $this->GalaxyUserServer->getUserinfo($this);
        $response = [
            "code" => 200,
            "data" => $this->userinfo->toArray(),
            "msg" =>"获取用户信息成功"
        ];
        return $this->response->setJsonContent($response);
    }

    /**
     * 注册会员
     */
    public function registerUserAction(){
        try{
            $this->db->begin();
            $this->GalaxyUserServer->mobile = $this->request->getPut("mobile");
            if (! preg_match("/^0?(13|14|15|17|18)[0-9]{9}$/", $this->GalaxyUserServer->mobile)){
                throw new \Exception("手机号码无效！");
            }
            $code = (int)$this->request->getPut("code");
//            if($code != 8888){
                $sms_obj = $this->di->get("SmsHelper");
                $code_res = $sms_obj->validateCode($this->GalaxyUserServer->mobile,$code);
                if($code_res["code"] == 0){
                    throw new \Exception($code_res["msg"]);
                }
//            }
            $response = $this->GalaxyUserServer->registerUser($this);
            $this->db->commit();
            return $this->response->setJsonContent($response);
        }catch(\Exception $e){
            $this->db->rollback();
            $response = [
              "code"=>0,
              "data"=>'',
              "msg"=>$e->getMessage()
            ];
            return $this->response->setJsonContent($response);
        }
    }
}

