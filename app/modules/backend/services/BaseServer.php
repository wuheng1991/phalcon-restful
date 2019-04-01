<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 11:46
 */

namespace Backend\Services;
use Phalcon\Mvc\Controller;

class BaseServer extends Controller
{
    //设置返回参数
    public $msg = '系统有误';
    public $code = 0;
    public $data = false;

    //返回值
    public function returnData(){
        return $response = [
            "code" => $this->code,
            "msg" => $this->msg,
            "data" => $this->data,
        ];
    }
    /**
     * description 对上传base64图片的处理
     * @param string $base64_img
     * @param string $dir
     * @param string $name
     * @return data
     */
    public function dealBase64ThumbService($base64_img, $dir, $name){
        $ret = [ 'msg' => '', 'code' => 0, 'data' => false];
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                $random = mt_rand(1, 1000);
                $new_file = $dir.$name.'-'.date('YmdHis').'-'.$random.'.'.$type;
                $base64_new = str_replace($result[1],'', $base64_img);
                if(file_put_contents($new_file, base64_decode($base64_new))){
                    //$img_path = str_replace('.', '', $new_file);
                    $ret = array(
                        'msg' => '上传图片成功',
                        'code' => 200,
                        'data' => ltrim($new_file,'.')
                    );
                }else{
                    $ret['msg'] = '上传图片失败,字段：'.$name;
                }
            }else{
                $ret['msg'] = '上传文件类型错误,字段：'.$name;
            }
        }else{
            $ret['msg'] = '上传文件错误,字段：'.$name;
        }

        return $ret;
    }

    /**
     * 文件上传接口
     */
    public function dealFileUploadService($params,$apiUrl)
    {
        header("content-Type: text/html; charset=Utf-8");

        $ret = [ 'msg' => '', 'code' => 0, 'data' => false];
        //定义文件格式
        $fileTypeArray = ['image/jpeg', 'image/gif', 'image/png'];

        if($params){
            //定义上传文件根目录
            $time = date("Ymd", time());
            $filepath = "./img/backend/rich_text_file/" . $time . "/";
            //判断文件夹是否存在，不存在则创建文件夹
            is_dir($filepath) OR mkdir($filepath, 0777, true);

            foreach ($params as $file) {
                $uploadFile = $file->getName();
                $tempName = $file->getTempName();
                $fileSize = $file->getSize();
                $fileType = $file->getType();
                $errorno = $file->getError();
                $key = $file->getKey();
                $extension = $file->getExtension();

                if($errorno > 0){
                    $ret['msg'] = '上传失败';
                    return $ret;
                }

                if($fileSize > 50 * 1024 * 1024){
                    $ret['msg'] = '文件大小不能超过50M';
                    return $ret;
                }

                if(!in_array($fileType, $fileTypeArray)){
                    $ret['msg'] = '该图片格式异常';
                    return $ret;
                }

                //var_dump($uploadFile,$tempName,$fileSize,$fileType,$errorno,$key,$extension);
                //定义图片名称
                $imgTime = time();
                $mt_rand = mt_rand(10,100);
                $imgName = $filepath . $imgTime. '_'.$mt_rand. '.'.$extension;
                if (is_uploaded_file($tempName)) {
                    if (move_uploaded_file($tempName, $imgName)) {
                        return [ 'msg' => '上传成功', 'code' => 200, 'data' => $apiUrl.ltrim($imgName,'.')];
                    } else {
                        $ret['msg'] = '上传失败';
                        return $ret;
                    }
                } else {
                    $ret['msg'] = '不是合法文件';
                    return $ret;
                }

            }
        }else{
            $ret['msg'] = '非法操作';
            return $ret;
        }
    }

    public function trimAll($str)//删除空格
    {
        $oldchar=array(" ","　","\t","\n","\r");
        $newchar=array("","","","","");
        return str_replace($oldchar,$newchar,$str);
    }

    public function dg($array,$pid)
    {
        $arr = array();
        $temp = array();
        foreach ($array as $v) {
            if ($v['pid'] == $pid) {
                $temp = self::dg($array, $v['id']);
                //判断是否存在子数组
                if($temp)
                {
                    $v['children'] = $temp;
                }
                $arr[] = $v;
            }
        }
        return $arr;
    }

    /**
     * 微信消息推送message
     * @return mixed
     */
    public function getWechatPutMessage($openid, $template_id, $template_data, $template_url='', $template_color='#173177'){
        switch ($template_id){
            // 订单发货通知
            case 'order_send_message':
                $keyword_array = [
                    'first' => ['value'=>isset($template_data['first']) ? $template_data['first'] : '','color'=>$template_color],#头部信息
                    'keyword1' => ['value' => isset($template_data['keyword1']) ? $template_data['keyword1'] : '','color'=>$template_color],
                    'keyword2' => ['value' => isset($template_data['keyword2']) ? $template_data['keyword2'] : '','color'=>$template_color],
                    'keyword3' => ['value' => isset($template_data['keyword3']) ? $template_data['keyword3'] : '','color'=>$template_color],
                    'keyword4' => ['value' => isset($template_data['keyword4']) ? $template_data['keyword4'] : '','color'=>$template_color],
                    'keyword5' => ['value' => isset($template_data['keyword5']) ? $template_data['keyword5'] : '','color'=>$template_color],
                    'remark' => ['value'=>isset($template_data['remark']) ? $template_data['remark'] : '','color'=>$template_color],#描述
                ];

                return [
                    'touser'=>$openid,
                    'template_id'=>$template_id,
                    'url'=> $template_url,
                    'data'=> $keyword_array
                ];
                break;
            // 积分兑换通知
            case 'integral_exchange':
                $keyword_array= [
                    'first' => ['value'=>isset($template_data['first']) ? $template_data['first'] : '','color'=>$template_color],#头部信息
                    'keyword1' => ['value' => isset($template_data['keyword1']) ? $template_data['keyword1'] : '','color'=>$template_color],
                    'keyword2' => ['value' => isset($template_data['keyword2']) ? $template_data['keyword2'] : '','color'=>$template_color],
                    'keyword3' => ['value' => isset($template_data['keyword3']) ? $template_data['keyword3'] : '','color'=>$template_color],
                    'keyword4' => ['value' => isset($template_data['keyword4']) ? $template_data['keyword4'] : '','color'=>$template_color],
                    'remark' => ['value'=>isset($template_data['remark']) ? $template_data['remark'] : '','color'=>$template_color],#描述
                ];

                return [
                    'touser'=>$openid,
                    'template_id'=>$template_id,
                    'url'=> $template_url,
                    'data'=> $keyword_array
                ];
                break;
            default:
                return '';
                break;
        }
    }
}