<?php

namespace Api\Services;

use Api\Models\GalaxyWechatClient;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 14:43
 */
class GalaxyWechatClientServer extends BaseServer
{
    public function getWechatToken($obj)
    {
        $obj->di->get("wechat")->getAccesstoken($obj->code);
        file_put_contents("access_token.txt",json_encode($obj->wechat));
        if (empty($obj->wechat->access_token["errcode"])) {
            $obj->wechat->getuserinfo($obj);
            $userinfo = $this->wechatUserCheckIsset($obj->wechat->access_token["openid"]);
            if ($userinfo["code"] == 0) {
                $response = $this->addWechatUser($obj->wechat);
                if (!empty($response)) {
                    $arr["id"] = $response;
                    $arr["nick_name"] = $obj->wechat->userinfo["nickname"];
                    $arr["openid"] = $obj->wechat->access_token["openid"];
                    $token = self::createToken($arr);
                    return $response = [
                        "code" => 200,
                        "data" => $token,
                        "msg" => "获取token成功"
                    ];

                }
                return $response = [
                    "code" => 0,
                    "data" => "",
                    "msg" => "新建微信用户失败"
                ];
            }
            $updatedata["access_token"] = $obj->wechat->access_token["access_token"];
            $updatedata["refresh_token"] = $obj->wechat->access_token["refresh_token"];
            $updatedata["access_create_time"] = date("Y-m-d H:i:s", time());
            $wechat_obj = new GalaxyWechatClient();
            $where["where"] = "openid = :openid:";
            $where["value"]["openid"] = $obj->wechat->userinfo["openid"];
            $update_res = $wechat_obj->updates($where, $updatedata);
            if (!empty($update_res)) {
                $arr["id"] = $userinfo["data"]["id"];
                $arr["nick_name"] = $userinfo["data"]["nick_name"];
                $arr["openid"] = $userinfo["data"]["openid"];
                $token = self::createToken($arr);
                return  $response = [
                    "code" => 200,
                    "data" => $token,
                    "msg" => "获取token成功"
                ];
            }
            return $response = [
                "code" => 0,
                "data" => "",
                "msg" => "更新access_token到本地失败"
            ];
        }
        return $response = [
            "code" => 0,
            "data" => "",
            "msg" => $obj->wechat->access_token["errmsg"]
        ];
    }

    /**
     * 判断用户是否已经存在
     * @param $openid
     * @return array
     */
    public function wechatUserCheckIsset($openid)
    {
        $wechat_client_obj = new GalaxyWechatClient();
        $client_info = $wechat_client_obj->findFirst("openid='" . $openid . "'");
        $response = [
            "code" => 0,
            "data" => "",
            "msg" => "用户不存在"
        ];
        if (!empty($client_info)) {
            $result = [
                'id' => $client_info->id,
                'nick_name' => $client_info->nick_name,
                'thumb' => $client_info->thumb,
                'openid' => $client_info->openid,
                'name' => $client_info->name,
                'phone' => $client_info->phone,
                'phone_address' => $client_info->phone_address,
                'is_care' => $client_info->is_care,
                'is_deleted' => $client_info->is_deleted,
                'sort' => $client_info->sort,
                'login_ip' => $client_info->login_ip,
                'last_login_time' => $client_info->last_login_time,
                'create_time' => $client_info->create_time,
                'update_time' => $client_info->update_time,
                'vip' => $client_info->vip,
                'access_token' => $client_info->access_token,
                'refresh_token' => $client_info->refresh_token,
                'access_create_time' => $client_info->access_create_time,
            ];
            $response = [
                "code" => 200,
                "data" => $result,
                "msg" => "查询成功"
            ];
        }
        return $response;
    }

    /**
     * 添加微信用户到本地库
     * @param $parmas
     * @return bool
     */
    public function addWechatUser($parmas)
    {
        $response = false;
        $wechat_client_obj = new GalaxyWechatClient();
        $data['access_token'] = $parmas->access_token["access_token"];
        $data['refresh_token'] = $parmas->access_token["refresh_token"];
        $data['openid'] = $parmas->access_token["openid"];
        $data['access_create_time'] = date("Y-m-d H:i:s", time());
        $data["nick_name"] = $parmas->userinfo["nickname"];
        $data["sex"] = (int)$parmas->userinfo["sex"];
        $data["language"] = $parmas->userinfo["language"];
        $data["city"] = $parmas->userinfo["city"];
        $data["province"] = $parmas->userinfo["province"];
        $data["country"] = $parmas->userinfo["country"];
        $data["thumb"] = $parmas->userinfo["headimgurl"];
        $data["privilege"] = json_encode($parmas->userinfo["privilege"]);
        $add_res = $wechat_client_obj->add($data, true);
        if ($add_res) {
            $response = $add_res;
        }
        return $response;
    }

    /**
     * 生成token
     * @param $arr
     * @return mixed
     */
    protected static function createToken($arr)
    {
        $crypt = new \Phalcon\Crypt();
        $string = json_encode($arr);
        $token = $crypt->encryptBase64($string, GALAXY_WX_API_SECRET_KEY);#加密
        return $token;
    }

    /**
     * 获取分享签名等config
     */
    public function getGrantConfig($url){
        $access_token = json_decode($this->redis->get('token:grant_token:'));
        if(empty($access_token)){
            $grant_token = $this->wechat->getGrantToken();
            if(empty($grant_token["access_token"])){
                $this->msg = "获取全局token失败";
                return $this->returnData();
            }
            $access_token = $grant_token["access_token"];
            $this->redis->save('token:grant_token:', json_encode($grant_token["access_token"]),7200);
        }
        $Ticket["ticket"] = json_decode($this->redis->get('Ticket:Ticket_config:'));
        if(empty($Ticket["ticket"])){
            $Ticket = $this->wechat->getTicket($access_token);
            if($Ticket["errcode"] != 0){
                $this->msg = "获取Ticket失败";
                return $this->returnData();
            }
            $this->redis->save('Ticket:Ticket_config:', json_encode($Ticket["ticket"]),7200);
        }
        $data['noncestr'] = $this->str_rand(16);
        $data['appid'] = \wechat::appid;
        $data['timestamp'] = time();
        $data['ticket'] = $Ticket["ticket"];
        $data['combine_str'] = "jsapi_ticket=".$data["ticket"]."&noncestr=".$data['noncestr']."&timestamp=".$data['timestamp']."&url=".$url;
        $data['signature'] = sha1($data['combine_str']);
        $this->redis->save('signature:signature_config:', json_encode($data),7200);
        $this->msg = "请求成功";
        $this->code = 200;
        $this->data = $data;
        return $this->returnData();
    }

    /**
    * 生成随机字符串
    * @param int $length 生成随机字符串的长度
    * @param string $char 组成随机字符串的字符串
    * @return string $string 生成的随机字符串
    */
    protected function str_rand($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
     if(!is_int($length) || $length < 0) {
            return false;
     }
     $string = '';
     for($i = $length; $i > 0; $i--) {
                 $string .= $char[mt_rand(0, strlen($char) - 1)];
     }
     return $string;
}

    /**
     * 获取系统token（非微信token）
     * @param $id
     * @return array
     */
    public function getToken($id){
        $response = [
            "code" => 0,
            "data" => '',
            "msg" => "查询成功"
        ];
        $client_obj = new GalaxyWechatClient();
        $where["where"] = "id = :id:";
        $where["value"]["id"] = $id;
        $info = $client_obj->findone($where);
        if(!empty($info)){
            $info_arr =  $info->toArray();
            $arr["id"] = $info_arr["id"];
            $arr["nick_name"] = $info_arr["nick_name"];
            $arr["openid"] = $info_arr["openid"];
            $token = self::createToken($arr);
            $response = [
                "code" => 200,
                "data" => $token,
                "msg" => "获取token成功"
            ];
        }
        return $response;
    }



}