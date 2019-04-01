<?php
namespace Api\Modules\Api\Controllers;

use Api\Models\GalaxyWechatClient;
use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
    public function initialize(){
        try{
            $token = $this->getTokenAction();
            if(empty($token)){
                throw new \Exception("token不允许为空");
            }
            $token_arr = $this->deciphering($token);
            if(empty($token_arr)){
                throw new \Exception("token无效");
            }
            $client_obj = new GalaxyWechatClient();
            $where["where"] = "id = :id: and openid = :openid: and is_deleted = :is_deleted:";
            $where["value"]["id"] = $token_arr["id"];
            $where["value"]["openid"] = $token_arr["openid"];
            $where["value"]["is_deleted"] = 0;
            $client_info = $client_obj->findone($where,"*");
            if(empty($client_info)){
                throw new \Exception("token无效");
            }
            $this->id = $client_info->id;
            $this->nick_name = $client_info->nick_name;
            $this->name = $client_info->name;
            $this->openid = $client_info->openid;
            $this->phone = $client_info->phone;
            $this->vip = $client_info->vip;
            $this->points = $client_info->points;
            # 修改登陆时间
            $update_data['last_login_time'] = date('Y-m-d H:i:s',time());
            $client_obj->updates($where,$update_data);
        }catch (\Exception $e){
            $response = [
                "code"=>0,
                "data"=>"",
                "msg"=>$e->getMessage(),
            ];
            echo json_encode($response);exit;
        }
    }

    /**
     * 获取请求中的token
     * @return mixed
     */
    protected function getTokenAction(){
        $request = $this->request;
        $headers = $request->getHeaders();
        $httpAuthorization = isset($headers ['Token'])?$headers ['Token']:"";
        return $httpAuthorization;
    }

    /**
     * token解密
     * @param $string
     * @return mixed
     */
    protected function deciphering($string){
        $crypt = new \Phalcon\Crypt();
        $token = json_decode($crypt->decryptBase64($string, GALAXY_WX_API_SECRET_KEY),true);#解密
        return $token;
    }

    public function error($response){
        return $this->response->setJsonContent($response);
    }

    
}
