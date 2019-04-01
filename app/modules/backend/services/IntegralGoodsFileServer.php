<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/29
 * Time: 13:32
 */
namespace Backend\Services;
use Api\Models\GalaxyWechatActivity;

class IntegralGoodsFileServer extends BaseServer
{
    /**
     * 增添图片
     * @return mixed
     */
    public function addfileUploadService($fileParams, $postParams, $apiUrl){
        $result = ['msg' => '', 'code' => 0, 'data' => false];

        header("content-Type: text/html; charset=Utf-8");

        $ret = [ 'msg' => '', 'code' => 0, 'data' => false];
        //定义文件格式
        $fileTypeArray = ['image/jpeg', 'image/gif', 'image/png'];
        $fileArray = [];

        if($fileParams){
            //定义上传文件根目录
            $time = date("Ymd", time());
            $filepath = "./img/backend/integral_goods/" . $time . "/";
            //判断文件夹是否存在，不存在则创建文件夹
            if(!file_exists($filepath)){
                mkdir($filepath, 0777, true);
            }

            foreach ($fileParams as $key => $file) {
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
                $mt_rand = mt_rand(10,1000);
                $imgName = $filepath . $imgTime. '_'.$key.'_'.$mt_rand. '.'.$extension;
                if (is_uploaded_file($tempName)) {
                    if (move_uploaded_file($tempName, $imgName)) {
                        $imgUrl = ltrim($imgName,'.');
                        $imgUrlArray = explode('/', $imgUrl);
                        $imgArray = array(
                            'url' => $apiUrl.$imgUrl,
                            'name' => $imgUrlArray[4].'/'.$imgUrlArray[5],
                        );
                        array_push($fileArray, $imgArray);
                        return [ 'msg' => '上传成功', 'code' => 200, 'data' => $fileArray];
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

        return $result;
    }
}