<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 13:40
 */
use Phalcon\Config;
class wechat{
//    const redirect_uri = "https://test.wechat.galaxy-immi.com/auth";
    const redirect_uri = WECHAT_REDIRECT_URI;
//    const appid = "wx22d79f903527837b";
    const appid = APPID;
//    const appsecret = "41530e84b67dcb5f897267605b3f034e";
    const appsecret = APPSECRET;
    const response_type = "code";
    const scope = "snsapi_userinfo";
    public $state = "/";
    public $code;
    public $access_token;
    public $userinfo;
    public $token;

    # 消息推送模板设置
    public $template_array = [
        'order_send_message'=>'UpTi-vNZOYD9V9HNP4ZPIfjyou9bL_rcJR9M1J5j7g0', # 订单发货通知
        'integral_exchange'=>'VdttoE7NXnXBsaXJKZpmReHbL-x_TVlz6pVdzT-nfhk', # 积分兑换通知
    ];

    public function getUserCode($state=""){
        if(!empty($state)){
            $this->state = urlencode($state);
        }
       $redirect_uri = urlencode(self::redirect_uri);
       return  'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.self::appid."&redirect_uri=".$redirect_uri."&response_type=".self::response_type."&scope=".self::scope."&state=". $this->state."#wechat_redirect";
    }

    public function getAccesstoken($code){
        $this->code = $code;
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".self::appid."&secret=".self::appsecret."&code=".$this->code."&grant_type=authorization_code";
        $this->access_token = json_decode($this->curl_https("get",$url),true);
        return $this;
    }

    public function getuserinfo($obj){
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$obj->wechat->access_token["access_token"]."&openid=".$obj->wechat->access_token["openid"]."&lang=zh_CN";
        $this->userinfo = json_decode($this->curl_https("get",$url),true);
        return $this;
    }

    public function curl_https($mode = 'post', $url = '', $data = '')
    {
        $return = '';
        if ($mode == 'post') {
            $curl = curl_init(); // 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
            #curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
            curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
            $tmpInfo = curl_exec($curl); // 执行操作
            if (curl_errno($curl)) {
                echo 'Errno' . curl_error($curl);//捕抓异常
            }
            curl_close($curl); // 关闭CURL会话
            $return = $tmpInfo; // 返回数据，json格式
        } else {
            $curl = curl_init(); // 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
            $tmpInfo = curl_exec($curl);     //返回api的json对象
            //关闭URL请求
            curl_close($curl);
            $return = $tmpInfo;    //返回json对象
        }
        return $return;
    }

    public function getGrantToken(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".self::appid."&secret=".self::appsecret;
        $token = json_decode($this->curl_https("get",$url),true);
        $this->token = $token;
        return $token;
    }

    public function getTicket($token){
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$token."&type=jsapi";
        $ticket = json_decode($this->curl_https("get",$url),true);
        return $ticket;
    }


    //发送模版消息
    # openid 这里是用户的openid
    # template_id 模板ID
    public function pushMessage($message){

        //获取token
        $this->getGrantToken();

         // 验证模板id的合法性
        if(empty($message['template_id']) || !isset($this->template_array[$message['template_id']])){
            return false;
        }
        $message['template_id'] = $this->template_array[$message['template_id']];
        $data = json_encode($message);
        //发送
        $res = $this->sendTemplate($data);
        return $res;
    }

    #发送模板消息
    public function sendTemplate($data){
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->token['access_token'];
        $res = json_decode($this->curl_https("post",$url, $data),true);
        return $res;
    }

}