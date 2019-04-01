<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/14
 * Time: 13:47
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Phalcon\DI;
use Phalcon\Mvc\Dispatcher;
use Backend\Services\ActivityFileServer;

class ActivityFileController extends ControllerBase
{
    public function onConstruct()
    {
        #parent::onConstruct();//继承父类构造

        $this->ActivityFileServer = new ActivityFileServer();
    }

    public function index(){

    }

    public function createAction(){
        $apiUrl = di('config')->wechat_back_url;
        $fileParams = $this->request->getUploadedFiles();
        $postParams = $this->request->getPost();

        $ret = $this->ActivityFileServer->addfileUploadService($fileParams, $postParams, $apiUrl);
        return $this->response->setJsonContent($ret);
    }
}