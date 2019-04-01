<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/22
 * Time: 14:22
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Api\Models\GalaxyWechatActivityUser;
use Phalcon\DI;
use Phalcon\Mvc\Dispatcher;

class ActivityUserController extends ControllerBase
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
            $galaxyWechatActivityUserModel = new GalaxyWechatActivityUser();
            $ret = $galaxyWechatActivityUserModel->searchData($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function exportzipAction($id){
        $params = $this->request->get();
        $galaxyWechatActivityUserModel = new GalaxyWechatActivityUser();
        $ret = $galaxyWechatActivityUserModel->exportZipData($id, $params);
        return $this->response->setJsonContent(['msg'=>'导出成功', 'code'=>200, 'data'=>true]);
    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function exportexcelAction($id){
        $params = $this->request->get();
        $galaxyWechatActivityUserModel = new GalaxyWechatActivityUser();
        $ret = $galaxyWechatActivityUserModel->exportExcelData($id, $params);
        return $this->response->setJsonContent(['msg'=>'导出成功', 'code'=>200, 'data'=>true]);
    }

    public function exportqrcodeAction(){
        $id = 40;
        $name = "test";
        $path = "./activity-".$id;

//         exec("
////         cd /img/backend/qrcode/
////         zip -r $name.zip $path",$output);
        exec("
         cd img/backend/qrcode/
         pwd
         zip -r $name.zip $path
         ",$output);
         var_dump($output);
         exit;
    }
}