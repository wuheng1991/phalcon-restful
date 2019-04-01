<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 19:24
*/

namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Api\Models\GalaxyWechatClient;
use Phalcon\DI;
use Phalcon\Mvc\Dispatcher;

class ClientController extends ControllerBase
{
    protected $_method;
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
    public function getAction($id){
        $this->_method = $this->request->getMethod();
        if($id){
            $galaxyWechatClientModel = new GalaxyWechatClient();

            $ret = $galaxyWechatClientModel->getData($id);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: put
     * @return json
     */
    public function saveAction($id){
        if($id){
            $params = $this->request->getPut('name');
            $galaxyWechatClientModel = new GalaxyWechatClient();
            $ret = $galaxyWechatClientModel->saveData($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function searchAction(){
        $params = $this->request->get();
        $galaxyWechatClientModel = new GalaxyWechatClient();
        $ret = $galaxyWechatClientModel->searchData($params);
        return $this->response->setJsonContent($ret);

    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function countAction(){
        $galaxyWechatClientModel = new GalaxyWechatClient();
        $ret = $galaxyWechatClientModel->countData();
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function trendAction(){
        $params = $this->request->get();
        $galaxyWechatClientModel = new GalaxyWechatClient();
        $ret = $galaxyWechatClientModel->trendData($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function exportexcelAction(){
        $params = $this->request->get();
        $galaxyWechatClientModel = new GalaxyWechatClient();
        $ret = $galaxyWechatClientModel->exportExcelData($params);
        return $this->response->setJsonContent(['msg'=>'导出成功', 'code'=>200, 'data'=>true]);
    }
}