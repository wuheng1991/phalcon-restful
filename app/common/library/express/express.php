<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 6:34
 */
namespace express;
class express
{
    private $url='https://poll.kuaidi100.com/poll/query.do';
    private $key='';
    private $customer='';

    public function getExpress($param){
        $data['customer'] = $this->customer;
        $data['sign'] = strtoupper(MD5(json_encode($param).$this->key.$data['customer']));
        $data['param'] = json_encode($param);
        $post = 'customer='.$data['customer']."&sign=".$data['sign']."&param=". $data['param'];
        $res = self::curlData($this->url,$post);
        return $res;
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

}