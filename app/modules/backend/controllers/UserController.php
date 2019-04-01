<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/25
 * Time: 15:53
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Api\Models\GalaxyAdmin;
use Backend\Services\AuthServer;

class UserController extends ControllerBase
{
    /**
     * Method Http accept: get
     * @return json
     */
    public function getAction(){
        $result = ['msg' => '','code' =>  0,'data' => false];
        $userinfo = $this->userinfo;
        if($userinfo){
            $result['msg'] = '获取用户信息成功';
            $result['code'] = 200;
//            $userinfo['id'] = (int)$userinfo['id'];
            $result['data'] = $userinfo;
            $this->authServer = new AuthServer();
            $params['role_id'] = (int)$userinfo['roleid'];
            $result['data']['auth_tree'] = $this->authServer->getAuthTreesServer($params);
        }

        return $this->response->setJsonContent($result);
    }

    public function logoutAction(){
        $userinfo = $this->userinfo;
        if($userinfo){
            $token_key = 'galaxy_admin:token:'.$userinfo['id'];
            $this->redis->delete($token_key);
        }
        $this->session->destroy();
        $result=[
            'msg' => '退出成功',
            'code' => 200,
            'data' =>true
        ];
        return $this->response->setJsonContent($result);
    }
}