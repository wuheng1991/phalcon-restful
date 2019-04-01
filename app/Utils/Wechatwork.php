<?php

namespace App\Utils;

//use Yii;
//use yii\web\Controller;
//use yii\filters\VerbFilter;
//use yii\filters\AccessControl;
//use backend\models\GalaxyCrmClient;
//use backend\models\GalaxyCrmRecord;
//use backend\models\GalaxySysRole;
//use backend\models\GalaxyAdmin;
//use backend\models\GalaxySysFilter;


class Wechatwork
{
    //相关配置
    private $url = 'https://qyapi.weixin.qq.com/';
    // private $corpid = '';
    // private $corpsecret = '';
    // private $apply = array('crm'=>1000004,'activity'=>1000007);

    private $project =  array(
                            'test'=>array('apply'=>1,'corpid'=>'1','corpsecret'=>'1')
                        );

    //设置获取的token(标签ID列表)
    private $token = '';
    //对应的属性(成员ID列表)
    private $touser = '';
    //对应的属性(部门ID列表)
    private $toparty = '';
    //对应的属性(标签ID列表)
    private $totag = '';

    //获取accesstoken
    public function accesstoken($type = ''){
        $this->redis = new Redis();
        $redisKey = 'qy'.$type.':accesstoken:json';
        $accesstoken = $this->redis->get($redisKey);
        $accesstoken = json_decode($accesstoken,true);
        if(empty($accesstoken['access_token'])){
            $url = $this->url.'/cgi-bin/gettoken?corpid='.$this->project[$type]['corpid'].'&corpsecret='.$this->project[$type]['corpsecret'];
            $data = $this->curl_https('get',$url);
            $return = json_decode($data,true);
            $accesstoken['time'] = time();
            $accesstoken['access_token'] = $return['access_token'];

            if($return['errcode'] != 0){
                echo "获取失败;错误信息为:".$return['errmsg'];die;
            }
            $source = $this->redis->save($redisKey,json_encode($accesstoken));
            $token = $accesstoken['access_token'];
        }else{
            if($accesstoken['time']+7200 < time()){
                $url = $this->url.'/cgi-bin/gettoken?corpid='.$this->project[$type]['corpid'].'&corpsecret='.$this->project[$type]['corpsecret'];
                $data = $this->curl_https('get',$url);
                $return = json_decode($data,true);
                $accesstoken['time'] = time();
                $accesstoken['access_token'] = $return['access_token'];

                if($return['errcode'] != 0){
                    echo "获取失败;错误信息为:".$return['errmsg'];die;
                }
                $source = Yii::$app->redis->set($redisKey,json_encode($accesstoken));
                $token = $accesstoken['access_token'];

            }else{
                $token = $accesstoken['access_token'];
            }
        }
        $this->token = $token;
        return $token;
    }

    //获取用户userid
    public function createFlock(){
        $url = $this->url.'/cgi-bin/appchat/create?access_token='.$this->token;
        $postData['name'] = '少伟的群';
        $postData['userlist'] = ['PingHuanTing','zh','HuangWeiJie'];
        $data = $this->curl_https('post',$url,json_encode($postData));
        $return = json_decode($data,true);
        return $return;
    }

    //获取用户userid
    public function send(){
        $url = $this->url.'/cgi-bin/appchat/send?access_token='.$this->token;
        $postData['chatid'] = "1";
        $postData['msgtype'] = 'text';
        $postData['text']['content'] = '想吃什么尽管说，银行卡密码';
        $postData['safe'] = 1;
        $data = $this->curl_https('post',$url,json_encode($postData));
        $return = json_decode($data,true);
        return $return;
    }

    //读取成员
    public function userGet($userid = ''){
        $token = $this->accesstoken();
        if(!empty($userid)){
            $url = $this->url.'/cgi-bin/user/get?access_token='.$token.'&userid='.$userid;
            $data = $this->curl_https('get',$url);
            $return = json_decode($data,true);
        }
        return $return;
    }

    //读取部门列表
    public function departmentGet($id = ''){
        $token = $this->accesstoken('activity');
        if(empty($id)){
            $url = $this->url.'/cgi-bin/department/list?access_token='.$token;
        }else{
            $url = $this->url.'/cgi-bin/department/list?access_token='.$token.'&userid='.$id;
        }
        $data = $this->curl_https('get',$url);
        $return = json_decode($data,true);
        pr($return);
        return $return;
    }

    //获取部门成员
    public function simplelistGet($departmentId = '',$fetchChild = ''){
        $token = $this->accesstoken('activity');
        if(!empty($departmentId)){
            if(!empty($fetchChild)){
                $url = $this->url.'/cgi-bin/user/simplelist?access_token='.$token.'&department_id='.$departmentId.'&fetch_child='.$fetchChild;
            }else{
                $url = $this->url.'/cgi-bin/user/simplelist?access_token='.$token.'&department_id='.$departmentId;
            }
            $data = $this->curl_https('get',$url);
 
            $return = json_decode($data,true);
        }
        return $return;
    }

    //设置推送部门ID
    public function settingsToparty($topartyArray = array()){
        $toparty = '';
        if($topartyArray != ''){
            //将数组循环拼接
            foreach ($topartyArray as $key => $value) {
                $toparty .= $value.'|';
            }
            //去除掉最后一个|字符
            substr($toparty,0,strlen($toparty)-1);
            //设置对象
            $this->toparty = $toparty;
        }   
    }

    //设置推送成员ID
    public function settingsTouser($touserArray = array()){
        $touser = '';
        if($touserArray != ''){
            //将数组循环拼接
            foreach ($touserArray as $key => $value) {
                $touser .= $value.'|';
            }
            //去除掉最后一个|字符
            substr($touser,0,strlen($touser)-1);
            //设置对象
            $this->touser = $touser;
        }   
    }

    //设置推送成员ID
    public function settingsTotag($totagArray = array()){
        $totag = '';
        if($totagArray != ''){
            //将数组循环拼接
            foreach ($totagArray as $key => $value) {
                $totag .= $value.'|';
            }
            //去除掉最后一个|字符
            substr($totag,0,strlen($totag)-1);
            //设置对象
            $this->totag = $totag;
        }   
    }

    //推送消息
    public function sendMessage($applyType,$msgType,$msgs){

        //对应的属性(成员ID列表)
        if(!empty($this->touser)){
            $msg['touser'] = $this->touser;
        }
        
        //对应的属性(部门ID列表)
        if(!empty($this->toparty)){
            $msg['toparty'] = $this->toparty;
        }
        
        //对应的属性(标签ID列表)
        if(!empty($this->totag)){
            $msg['totag'] = $this->totag;
        }

        $url = $this->url.'/cgi-bin/message/send?access_token='.$this->token;
        $msg['msgtype'] = $msgType;
        $msg['agentid'] = $this->project[$applyType]['apply'];
        $msg[$msgType]['content'] = $msgs;
        $msg = json_encode($msg);
        $data = $this->curl_https('post',$url,$msg);
        $return = json_decode($data,true);
        return $return;
    }

    //发起请求
    public function curl_https($mode = 'post', $url = '', $data = '')
    {
        $return = '';
        if ($mode == 'post') {
            $curl = curl_init(); // 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
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
    
}
