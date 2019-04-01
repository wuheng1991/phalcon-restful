<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 6:34
 */
namespace SmsHelper;
class SmsHelpers
{
    // 短信单个发送模板id
    private $_template_arr = [
        '2468',
    ];

    // 短信群发模板id
    private $_group_template_arr = [
        '1357',
    ];

    private $_pass_mobiles = [];

    private $_sign = '【测试】';

    const REQUEST_URL = "http://api.1cloudsp.com/api/v2/single_send";
    const REQUEST_GROUP_SEND_URL = "http://api.1cloudsp.com/api/v2/send"; //短信群发发送接口地址
    const REQUEST_STATUS_URL = "http://api.1cloudsp.com/report/status"; //获取短信状态接口地址
    const ACCESS_KEY = '';
    const SECRET = '';
    const TIME_OUT = 10;

    //验证码位数
    const CODE_NUM = 4;
    //每天的条数
    const DAY_NUM_LIMIT = 8;
    //两分钟间隔
    const RATE_LIMIT = 2;
    //有效期
    const EXPIRE_TIME = 5;
    /*
     * 设置签名
     * */
    function setSign($sign)
    {
        $this->_sign = $sign;
    }

    /*
     *生成验证码
     * */
    function createCode()
    {
        $str = '0123456789';
        $len = strlen($str)-1;
        $code = '';
        for($i = 0; $i < self::CODE_NUM; $i++){
            $num = mt_rand(0,$len);
            $code .= $str[$num];
        }
        return $code;
    }

    function saveCode($mobile, $code = '', $result_code = '')
    {
        $data = [
            'mobile' => $mobile,
            'validate_code' => $code,
            'result_code' => $result_code,
            'send_time' => time(),
            'is_used' => 0,
        ];
        $mobileLog = new \Api\Models\GalaxyMobilecodeLog();
        $save_result = $mobileLog->add($data);

        if ($save_result) {
            return $code;
        } else {
            return false;
        }
    }

    /*
     * 一天的验证码发次数是否超出
     * */
    function isBeyondNum($mobile)
    {
        $start_time = strtotime(date("Y-m-d") . " 00:00:00");
        $end_time = time();

        $mobileLog = GalaxyMobileLog::find()
            ->where([
                'mobile' => $mobile,
            ])
            ->andWhere(['>', 'send_time', $start_time])
            ->andWhere(['<', 'send_time', $end_time])
        ;

        return $mobileLog->count() > self::DAY_NUM_LIMIT ? true : false;
    }

    /*
     * 是否在间隔中
     * */
    function isBeyondRate($mobile)
    {
        $start_time = time() - self::RATE_LIMIT * 60;
        $mobileLog = GalaxyMobileLog::find()
            ->where([
                "mobile" => $mobile,
                "start_time" => $start_time
            ]);
        return $mobileLog->asArray()->all() ? true : false;
    }

    /*
     * 发送验证码
     * */
    function send($mobile, $template_id = '33541', $is_rate_beyond = false, $is_num_beyond = false)
    {
        if (in_array($mobile, $this->_pass_mobiles)) {
            return true;
        }
        if (! preg_match("/^0?(13|14|15|16|17|18|19)[0-9]{9}$/", $mobile)){
            return "手机号无效！";
        }
        if (! in_array($template_id, $this->_template_arr)) {
            throw new \Exception('模板id不存在！');
        }
//        if ($is_rate_beyond) {
//            if ($this->isBeyondRate($mobile)) {
//                return "请稍后再发！";
//            }
//        }
//        if ($is_num_beyond) {
//            if ($this->isBeyondNum($mobile)) {
//                return "今天的次数已经发完！";
//            }
//        }

        $where["where"] = "mobile = :mobile: and result_code = :result_code:";
        $where["value"]["mobile"] = $mobile;
        $where["value"]["result_code"] = 200;
        $update_data["is_used"] = 1;
        $MobilecodeLog = new \Api\Models\GalaxyMobilecodeLog();
        $MobilecodeLog->updates($where,$update_data);

        $validate_code = $this->createCode();
        $content = $validate_code;
        //$content = utf8_encode($content);
        $result = self::curlData(
            self::REQUEST_URL,
            [
                'accesskey' => self::ACCESS_KEY,
                'secret' => self::SECRET,
                'sign' => $this->_sign,
                'templateId' => $template_id,
                'mobile' => $mobile,
                'content' => $content,
            ]
        );
        if (!is_array($result)) {
            return array(0,"发送失败！");
        }
        $result_code = $result['code'] == 0 ? 200 : $result['code'];
        $save_result = $this->saveCode($mobile, $validate_code, $result_code);
        if ($result_code !== 200) {
            return array(0,"发送失败！");
//            return array(0,$result['msg']);
        }
        if ($save_result === false) {
            return array(0,"发送失败！");
//            return array(0,"发送记录保存失败,请重试");
        }
        return array(200,"发送成功");
    }


    /*
     * 验证验证码
     * */
    function validateCode($mobile, $code)
    {
        if (in_array($mobile, $this->_pass_mobiles)) {
            return array("msg"=>"验证成功!","code"=>1);
        }
        $pass_time = time()-self::EXPIRE_TIME * 60;

        //判断是否为数字
        if(!is_int($code)){
             return array("msg"=>"验证码错误,请输入正确的验证码!","code"=>0);
        }
        $arr = "mobile =".$mobile." and validate_code =".$code." and is_used = 0 and result_code = 200 and send_time >".$pass_time;
        $mobileLog = \Api\Models\GalaxyMobilecodeLog::findFirst($arr);
        if (empty($mobileLog)) {
            return array("msg"=>"验证码错误,请输入正确的验证码!","code"=>0);
        }
        $mobileLog->is_used = 1;
        $mobileLog->save();
        return array("msg"=>"验证成功!","code"=>200);
    }

    /**
     * CURL获取请求接口的数据
     * @param  string $mobile
     * @param  array $data 请求数据
     * @param  array $header 请求头信息数组
     * @param  string $method 请求方式
     * @return string || array 错误码或者数组数据
     */
    static  public function curlData($url, $data = [], $header = [], $method = "POST")
    {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
            //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
            curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        } else {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            return 'Errno' . curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return json_decode($result,true);
    }

    /**
     * [actionGetAreaByMobile 查询手机归属地]
     * @param  [type] $mobile [description]
     * @return [type]         [description]
     */
    public function actionGetAreaByMobile($mobile)
    {
        //补充归属地查询
        $server = "apis.juhe.cn";  //聚合数据
        $url = "/mobile/get" . "?phone=" . $mobile . "&key=090a28a17204bf8f341a995deb392991";
        $data = $this->curlData($server . $url,[],[],'get');
        $return['address'] = '';
        $return['province'] = '';
        $return['city'] = '';
        if (!empty($data)) {
            if ($data['resultcode'] == 200) {
                $return['address'] = $data['result']['province'] . '-' . $data['result']['city'];
                $return['province'] = $data['result']['province'];
                $return['city'] = $data['result']['city'];
            }
        }
        return $return;
    }

    /*
     * 短信群发
     * $accesskey 平台分配给用户的accesskey
     * $secret 平台分配给用户的secret
     * $sign 平台上申请的接口短信签名或者签名ID（须审核通过）
     * $templateId 平台上申请的接口短信模板Id
     * $mobile 接收短信的手机号码，多个号码以半角逗号,隔开
     * $content 发送的短信内容是模板变量内容，多个变量中间用##或者$$隔开
     * $data 该字段用于发送个性短信，mobile和content字段不需要填写，该字段json字符串，json的key是手机号，value是短信内容变量，等同于上面的content 包含多个变量中间用##或者$$隔开
     * $scheduleSendTime 短信定时发送时间，格式为：2018-01-01 18:00:00；参数如果为空表示立即发送
    */
    public function groupSentMessage($mobile, $template_id = '', $content = '', $data = '', $scheduleSendTime = ''){
        $temp_mobile_array = []; //存放有效的手机号
        $mobile_array = explode(',', $mobile);
        if($mobile_array){
            foreach($mobile_array as $k => $v){
                // 有效手机号验证
                if (!empty($v) && preg_match("/^0?(13|14|15|16|17|18|19)[0-9]{9}$/", $v)){
                    $temp_mobile_array[] = $v;
                }
            }
        }

        if(empty($temp_mobile_array)){
            return array(0,"活动群发有效手机号不能为空");
        }


        if (!empty($template_id) && !in_array($template_id, $this->_group_template_arr)) {
            return array(0, "群发模板id不存在!");
        }

        $result = self::curlData(
            self::REQUEST_GROUP_SEND_URL,
            [
                'accesskey' => self::ACCESS_KEY,
                'secret' => self::SECRET,
                'sign' => $this->_sign,
                'templateId' => $template_id,
                'mobile' => implode(',', $temp_mobile_array),
                'content' => $content,
                'data' => $data,
                'scheduleSendTime' => $scheduleSendTime
            ]
        );

        if (!is_array($result) || ($result['code'] != '0')) {
            return array($result['code'], $result['msg']);
        }

        return $result;
    }

    /*
     * 获取短信状态
     * $accesskey 平台分配给用户的accesskey
     * $secret 平台分配给用户的secret
    */
    public function statusMessage(){
        $result = self::curlData(
            self::REQUEST_STATUS_URL,
            [
                'accesskey' => self::ACCESS_KEY,
                'secret' => self::SECRET,
            ]
        );

        return $result;
    }
}