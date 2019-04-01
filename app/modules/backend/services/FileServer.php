<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/4
 * Time: 16:52
 */
namespace Backend\Services;
use Api\Models\GalaxyWechatActivity;

class FileServer extends BaseServer
{
    public function addfileUploadService($params, $apiUrl){
        $result = ['msg' => '', 'code' => 0, 'data' => false];
        //var_dump($params);exit;
        $ret = $this->dealFileUploadService($params, $apiUrl);
        if($ret['code'] == 200){
            return $ret;
        }else{
            return $ret;
        }

        return $result;
    }
}