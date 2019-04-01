<?php

namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Api\Models\GalaxyWechatActivity;
use Api\Models\GalaxyWechatActivityClient;
use Api\Models\GalaxyAdmin;
use Api\Modules\Backend\Models\GalaxyAdmin AS zhangsan;
//use Api\Modules\Backend\Models\GalaxyWechatActivity;
use Api\Modules\Backend\Models\GalaxyWechatActivityUser;

//use Api\models\GalaxyRoleAuth;
////use \Api\library\tool\phpqrcode;
//use Phalcon\Config\Adapter\Php as ConfigPhp;
//use Api\Models\GalaxyWechatActivity;
////use Api\Modules\Backend\Controllers\DbListenerController;
//use Phalcon\Events\Event;
//use Phalcon\Events\Manager as EventsManager;
//use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
//use Phalcon\Logger\Adapter\File as FileLogger;

class IndexController extends ControllerBase
{
    public $con;
    public $listen;


    public function onConstruct(){

    }

    public function indexAction(){

        pr($this->session);
//        $ret = $this->WechatTemplateServer->getToken();
//        $grantTokenRet = $this->di->get("wechat")->getGrantToken();
//        $token = $grantTokenRet['access_token'];

        $openid = 'oh20h1TNjxcXuo4blOd06ja2ftpk';
        $template_id = 'order_send_message';
        $message = [
            'touser'=>$openid,
            'template_id'=>$template_id,
            'url'=>'',
            'data'=>[
                'first'=>['value'=>'您好,您兑换的礼品已经发货！','color'=>'#173177'],#头部信息
                'keyword1'=>['value'=>'11','color'=>'#173177'], #兑换礼品
                'keyword2'=>['value'=>'22','color'=>'#173177'],#发货时间
                'keyword3'=>['value'=>'33','color'=>'#173177'],#物流公司
                'keyword4'=>['value'=>'44','color'=>'#173177'],#快递单号
                'keyword5'=>['value'=>'55','color'=>'#173177'],#收货地址
                'remark'=>['value'=>'快递将很快到达您的手中，请注意查收哦！','color'=>'#173177'],#描述
            ]
        ];

        $messageRet = $this->di->get("wechat")->pushMessage($message);
        return $this->response->setJsonContent($messageRet);

    }
}

