<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/27
 * Time: 19:15
 */

namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Backend\Services\GalaxyWechatIntegralStatsServer;


class IntegralStatsController extends ControllerBase
{
    public function onConstruct()
    {
        #parent::onConstruct();//继承父类构造

        $this->galaxyWechatIntegralStatsServer = new GalaxyWechatIntegralStatsServer();
    }

    /**
     * Method Http accept: get
     * @return data
     * 积分收支/分统计
     */
    public function statsCountAction()
    {
        $params = $this->request->get();
        $ret = $this->galaxyWechatIntegralStatsServer->statsCountService($params);
        return $this->response->setJsonContent($ret);
    }
}
