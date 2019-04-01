<?php
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Phalcon\Mvc\Controller;
use Api\Models\GalaxyAdmin;

class ControllerBase extends Controller
{
    const GALAXY_WX_API = 'galaxy_xw_api';

    public $userinfo = [];
    
    /**
     * 初始化操作并判断是否登录
     * @return mixed
     */
    public function initialize()
    {

        $result = ['msg' => '','code' =>  0,'data' => false];
        $time = time();
        $token_flag = true;

        $url = $this->request->getURI();
        $parse_url = parse_url($url);
        $config_url = $parse_url['path'];

        //接口是否需要验证
        $filterTokenData = $this->getFilterTokenAction();
        foreach($filterTokenData as $k => $v){
            if(stripos($config_url, $v) !== false){
                $token_flag = false;
                break;
            }
        }

        if($token_flag == true){
            $token = $this->getTokenAction();

            //判断token是否为空
            if(empty($token)){
                $result['code'] = -1000;
                $result['msg'] = 'token不能为空';
                echo $this->getJson($result);
                exit;
            }

            //解密token
            $crypt = new \Phalcon\Crypt();
            $token_str = $crypt->decryptBase64($token, self::GALAXY_WX_API);
            $token_array = explode('@@@',$token_str);
            //判断token结构是否合法
            if(!($token_array && isset($token_array[0]) && isset($token_array[1]))){
                $result['code'] = -1000;
                $result['msg'] = 'token不合法';
                echo $this->getJson($result);
                exit;
            }


            $user_array = json_decode($token_array[0], true);#用户信息
            $time_out = (int)$token_array[1];#过期时间
            $token_key = 'galaxy_admin:token:'.$user_array['id'];

            //判断是否失效
            if(!$this->redis->exists($token_key)){
                $result['code'] = -1000;
                $result['msg'] = 'token失效';
                echo $this->getJson($result);
                exit;
            }

            //判断token是否正确
            if($this->redis->get($token_key) != $token){
                $result['code'] = -1000;
                $result['msg'] = 'token验证失败';
                echo $this->getJson($result);
                exit;
            }


            //判断是否过期
            if($time_out < $time){
                $result['code'] = -1000;
                $result['msg'] = 'token过期';
                echo $this->getJson($result);
                exit;
            }

            //验证成功
            $this->userinfo = $user_array;

            $result = ['msg' => 'token 验证成功','code' =>  200,'data' => true];
        }

        return true;
    }


    /**
     * 产生token
     * @return mixed
     */
//    public function generateTokenAction($userinfo){
//        $crypt = new \Phalcon\Crypt();
//
//        $array['user_sign'] = json_encode($userinfo);
//        #设置过期时间
//        $array['time_out'] = strtotime("+1 days");
//        //唯一数
//        //$array['unique_str'] = md5(uniqid(md5(microtime(true)),true));  //生成一个不会重复的字符串
//        //拼接成字符串
//        $string = implode('@@@',$array);
//        //$token = base64_encode("{$string}");// 分隔符建议替换成其他的字符
//        $token = $crypt->encryptBase64($string, self::GALAXY_WX_API);#加密
//        return $token;
//    }

    /**
     * 获取请求中的token
     * @return mixed
     */
    public function getTokenAction(){
        $request = $this->request;
        //$headers = getallheaders();
        $headers = $request->getHeaders();
        $httpAuthorization = isset($headers ['Token'])?$headers ['Token']:"";
        return $httpAuthorization;
    }

    public function getJson($result=array()){
        return json_encode($result);
    }

    /**
     * 跳过token验证地址
     * @return mixed
     */
    public function getFilterTokenAction(){
         return [
             '/backend/client/exportexcel', //客户导出
             '/backend/activity_user/exportzip', //二维码导出
             '/backend/activity_client/exportexcel', //客户活动导出
             '/backend/integral-order/excel', //订单下载
             '/backend/integral-log/excel', //积分记录下载
         ];
    }

    function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

}
