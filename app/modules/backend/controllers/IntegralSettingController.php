<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/27
 * Time: 18:51
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Backend\Services\GalaxyWechatIntegralSettingServer;


class IntegralSettingController extends ControllerBase
{
    public function onConstruct()
    {
        #parent::onConstruct();//继承父类构造

        $this->galaxyWechatIntegralSettingServer = new GalaxyWechatIntegralSettingServer();
    }

    /**
     * Method Http accept: get
     * @return data
     * 积分配置列表
     */
    public function getAction()
    {
        $params = $this->request->get();
        $ret = $this->galaxyWechatIntegralSettingServer->getDataService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: put
     * @return json
     * 积分配置设置
     */
    public function saveAction(){
        $params = $this->request->getPut();
        $ret = $this->galaxyWechatIntegralSettingServer->saveDataService($params);
        return $this->response->setJsonContent($ret);
    }
}