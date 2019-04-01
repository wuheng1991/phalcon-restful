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
//    const GALAXY_WX_API = 'galaxy_wx_api';

    public function getWechatToken($obj)
    {
        $obj->di->get("wechat")->getAccesstoken($obj->code);
        if (empty($obj->wechat->access_token["errcode"])) {
            $obj->wechat->getuserinfo($obj);
            $userinfo = $this->wechatUserCheckIsset($obj->wechat->access_token["openid"]);
            if ($userinfo["code"] == 0) {
                $response = $this->addWechatUser($obj->wechat);
                if (!empty($response)) {
                    $arr["id"] = $obj->wechat->userinfo["id"];
                    $arr["nick_name"] = $obj->wechat->userinfo["nickname"];
                    $arr["openid"] = $obj->wechat->access_token["openid"];
                    $token = self::createToken($arr);
                    return $response = [
                        "code" => 200,
                        "data" => $token,
                        "message" => "获取token成功"
                    ];

                }
                return $response = [
                    "code" => 0,
                    "data" => "",
                    "message" => "新建微信用户失败"
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
                    "message" => "获取token成功"
                ];
            }
            return $response = [
                "code" => 0,
                "data" => "",
                "message" => "更新access_token到本地失败"
            ];
        }
        return $response = [
            "code" => 0,
            "data" => "",
            "message" => $obj->wechat->access_token["errmsg"]
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
            "message" => "用户不存在"
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
                "message" => "查询成功"
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
            $response = true;
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

}