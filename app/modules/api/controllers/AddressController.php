<?php

namespace Api\Modules\Api\Controllers;

use Api\Services\GalaxyAddressServer;

class AddressController extends ControllerBase
{
    public function onConstruct(){
        $this->GalaxyAddressServer = new GalaxyAddressServer();
    }

    /**
     * 收货地址列表
     * @return mixed
     */
    public function shippingAddressAction(){
        $response = $this->GalaxyAddressServer->getAddress($this->id);
        return  $this->response->setJsonContent($response);
    }

    /**
     * 获取收货地址详情
     * @param $id
     * @return mixed
     */
    public function getAction($id){
        if(empty($id)){
            $response = array(
                "code"=>0,
                "data"=>'',
                "msg"=>"参数错误",
            );
            return $this->response->setJsonContent($response);
        }
        $response = $this->GalaxyAddressServer->getoneAddress($id);
        return  $this->response->setJsonContent($response);
    }
    /**
     * 收货地址添加
     * @return mixed
     */
    public function createAction(){
        $params = $this->request->getPost();
        $tmp_arr = array(
            "gather_name"=>"收货人",
            "gather_phone"=>"联系电话",
            "province"=>"省份",
            "city"=>"城市",
            "district"=>"辖区",
            "detailed_address"=>"详细地址",
        );
        foreach($tmp_arr as $k=>$v){
            $response = array(
                "code"=>0,
                "data"=>'',
                "msg"=>$tmp_arr[$k]."不能为空",
            );
            if($k == 'gather_phone'){
                if (! preg_match("/^0?(13|14|15|17|18)[0-9]{9}$/", $params['gather_phone'])){
                    $response["msg"] = "手机号码无效";
                    return $this->response->setJsonContent($response);
                }
            }else{
                if(empty($params[$k])){
                    return $this->response->setJsonContent($response);
                }
            }
        }
        $params["galaxy_wechat_client_id"] = $this->id;
        $response = $this->GalaxyAddressServer->addAddress($params);
        return  $this->response->setJsonContent($response);
    }

    /**
     * 添加收货地址
     * @param $aid
     * @return mixed
     */
    public function saveAction($aid){
        if(empty($aid)){
            $response = array(
                "code"=>0,
                "data"=>'',
                "msg"=>"参数错误",
            );
            return $this->response->setJsonContent($response);
        }
        $params = $this->request->getPut();
        $tmp_arr = array(
            "gather_name"=>"收货人",
            "gather_phone"=>"联系电话",
            "province"=>"省份",
            "city"=>"城市",
            "district"=>"辖区",
            "detailed_address"=>"详细地址",
        );
        foreach($tmp_arr as $k=>$v){
            $response = array(
                "code"=>0,
                "data"=>'',
                "msg"=>$tmp_arr[$k]."不能为空",
            );
            if($k == 'gather_phone'){
                if (! preg_match("/^0?(13|14|15|17|18)[0-9]{9}$/", $params['gather_phone'])){
                    $response["msg"] = "手机号码无效";
                    return $this->response->setJsonContent($response);
                }
            }else{
                if(empty($params[$k])){
                    return $this->response->setJsonContent($response);
                }
            }
        }
        $response = $this->GalaxyAddressServer->updateAddress($params,$aid);
        return  $this->response->setJsonContent($response);
    }

    /**
     * 删除收货地址
     * @param $aid
     * @return mixed
     */
    public function deleteAction($aid){
        if(empty($aid)){
            $response = array(
                "code"=>0,
                "data"=>'',
                "msg"=>"参数错误",
            );
            return $this->response->setJsonContent($response);
        }
        $response = $this->GalaxyAddressServer->deleteAddress($aid);
        return  $this->response->setJsonContent($response);
    }


}

