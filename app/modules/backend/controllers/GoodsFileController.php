<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/29
 * Time: 13:30
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Phalcon\DI;
use Phalcon\Mvc\Dispatcher;
use Backend\Services\IntegralGoodsFileServer;

class GoodsFileController extends ControllerBase
{
    public function onConstruct()
    {
        #parent::onConstruct();//继承父类构造

        $this->integralGoodsFileServer = new IntegralGoodsFileServer();
    }

    public function index(){

    }

    public function createAction(){
        $apiUrl = di('config')->wechat_back_url;
        $fileParams = $this->request->getUploadedFiles();
        $postParams = $this->request->getPost();

        $ret = $this->integralGoodsFileServer->addfileUploadService($fileParams, $postParams, $apiUrl);
        return $this->response->setJsonContent($ret);
    }
}