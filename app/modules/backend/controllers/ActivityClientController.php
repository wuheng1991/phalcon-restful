<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/22
 * Time: 13:14
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Api\Models\GalaxyWechatActivityClient;
use Phalcon\DI;
use Phalcon\Mvc\Dispatcher;

class ActivityClientController extends ControllerBase
{
    /**
     * Method Http accept: get
     * @return json
     */
    public function indexAction(){

    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function searchAction($id){
        if($id){
            $params = $this->request->get();
            $galaxyWechatActivityClientModel = new GalaxyWechatActivityClient();
            $ret = $galaxyWechatActivityClientModel->searchData($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function exportexcelAction($id){
        $params = $this->request->get();
        $galaxyWechatActivityClientModel = new GalaxyWechatActivityClient();
        $ret = $galaxyWechatActivityClientModel->exportExcelData($id,$params);
        return $this->response->setJsonContent(['msg'=>'导出成功', 'code'=>200, 'data'=>true]);
    }
}