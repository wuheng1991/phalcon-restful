<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/19
 * Time: 19:25
 */
namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Api\Models\GalaxyAdmin;

class LoginController extends Controller
{

    const GALAXY_WX_API = 'galaxy_xw_api';
    /**
     * 产生token
     * @return mixed
     */
    public function generateTokenAction($userinfo){
        $crypt = new \Phalcon\Crypt();

        $array['user_sign'] = json_encode($userinfo);
        #设置过期时间
        $array['time_out'] = strtotime("+1 days");
        //唯一数
        //$array['unique_str'] = md5(uniqid(md5(microtime(true)),true));  //生成一个不会重复的字符串
        //拼接成字符串
        $string = implode('@@@',$array);
        //$token = base64_encode("{$string}");// 分隔符建议替换成其他的字符
        $token = $crypt->encryptBase64($string, self::GALAXY_WX_API);#加密
        return $token;
    }

    /**
     * Method Http accept: post
     * @return data
     */
//    public function indexAction(){
//
//    }

    public function createAction()
    {
        $result = ['msg' => '','code' =>  0,'data' => false];
        if ($this->request->isPost()) {
            $params = $this->request->getPost();

            $ret = GalaxyAdmin::findFirst(array(
                'columns' => 'id,username,ico,roleid',
                'conditions'=>"username = :username: AND password = :password: AND dimission = :dimission: AND is_deleted = :is_deleted:",
                'bind'=>array(
                    'username'=>$params['username'],
                    'password'=>md5($params['password']),
//                    'roleid'=>4,
                    'dimission'=>'false',
                    'is_deleted'=>0,
                )
            ));

            if($ret){
                $ret->id = (int)$ret->id;
                #产生token
                $token = $this->generateTokenAction($ret->toArray());
                if($token){
                    #存储登陆用户信息redis
                    $this->redis->save('galaxy_admin:token:'.$ret->id, $token);
//                  $this->session->set('galaxy_wx_api_data', json_encode($ret->toArray()));
                    $result=[
                        'msg' => '生产token成功',
                        'code' => 200,
                        'data' =>array('token'=>$token),
                    ];
                }else{
                    $result['msg'] = '产生的token不能为空';
                }

            }else{
                $result['msg'] = '登陆失败，请重新再试';
            }
            //$this->response->setContentType('application/json', 'UTF-8');
            return $this->response->setJsonContent($result);
        }

    }

    //获取微信公众产生的token
    public function getwechattokenAction(){
        $result = ['msg' => '','code' =>  0,'data' => false];
        $params = $this->request->get();
        if($this->request->isGet() == false){
            $result = ['msg'=>'请求方式有误', 'code' => 0, 'data' => false];
            return $this->response->setJsonContent($result);
        }
        
        //判断code是否为空
        if(!isset($params['code']) || empty($params['code']) || !isset($params['state']) || empty($params['state'])){
            $result = ['msg'=>'参数错误', 'code' => 0, 'data' => false];
            return $this->response->setJsonContent($result);
        }

        //通过code获取微信用户id
        $applyType = 'activity';
        $this->wechatwork->accesstoken($applyType);
        $useid = $this->wechatwork->userGetId($params['code']);

        if(!isset($useid['errcode']) || $useid['errcode'] != 0){
            $result = ['msg'=>'微信请求有误', 'code' => 0, 'data' => $useid];
            return $this->response->setJsonContent($result);
        }

        //判断该用户是否存在
        $ret = GalaxyAdmin::findFirst(array(
            'columns' => 'id,username,ico,roleid',
            'conditions'=>"work_wechat_id = :work_wechat_id: AND dimission = :dimission: AND is_deleted = :is_deleted:",
            'bind'=>array(
                'work_wechat_id'=>$useid['UserId'],
                'dimission'=>'false',
                'is_deleted'=>0,
            )
        ));

        //存在，产生token
        if($ret){
            $ret->id = (int)$ret->id;
            #产生token
            $token = $this->generateTokenAction($ret->toArray());
            if($token){
                #存储登陆用户信息redis
                $this->redis->save('galaxy_admin:token:'.$ret->id, $token);
                $result=[
                    'msg' => '生产token成功',
                    'code' => 200,
                    'data' =>array('token'=>$token),
                ];
            }else{
                $result['msg'] = '产生的token不能为空';
            }
        }else{
            $result['msg'] = '用户未绑定企业微信，请前往crm绑定';
        }

        return $this->response->setJsonContent($result);
    }

    // 获取微信公众配置
    public function getwechatconfigAction(){
        $state = md5(mt_rand(1, 1000));
        $result = [
            'msg' => '获取微信公众配置',
            'code' => 200,
            'data' => [
                'appid' => $this->config->workwechat_appid,
                'agentid' => $this->config->workwechat_agentid,
                'state' => $state,
            ]
        ];
        return $this->response->setJsonContent($result);
    }
}