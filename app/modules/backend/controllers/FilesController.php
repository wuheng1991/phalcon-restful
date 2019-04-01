<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/4
 * Time: 16:50
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Api\Models\GalaxyWechatActivity;
use Phalcon\DI;
use Phalcon\Mvc\Dispatcher;
use Backend\Services\FileServer;

class FilesController extends ControllerBase
{
    public function onConstruct()
    {
        #parent::onConstruct();//继承父类构造

        $this->fileServer = new FileServer();
    }

    public function index(){

    }

    public function createAction(){
        $apiUrl = di('config')->wechat_back_url;
        $params = $this->request->getUploadedFiles();
        $ret = $this->fileServer->addfileUploadService($params, $apiUrl);
        return $this->response->setJsonContent($ret);
    }
}