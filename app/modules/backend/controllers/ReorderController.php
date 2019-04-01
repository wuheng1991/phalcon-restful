<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 11:03
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Backend\Services\GalaxyReorderServer;


class ReorderController extends ControllerBase
{
    public function onConstruct()
    {
        #parent::onConstruct();//继承父类构造

        $this->galaxyReorderServer = new GalaxyReorderServer();
    }

    /**
     * Method Http accept: get
     * @return data
     * 客户预约签单列表
     */
    public function searchAction($id)
    {
//        $opt=$this->redis->getOptions();
//        pr($opt);
//        exit;
//
//        $opt['index']=1;
//        $this->modelsCache->setOptions($opt);
//
//        pr($this->redis->get('initialize:project:key'));

        //$this->redis->setIndex(1)->get('initialize:project:key');

        if(!empty($id)){
            $params = $this->request->get();
            $ret = $this->galaxyReorderServer->searchDataService($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }
}