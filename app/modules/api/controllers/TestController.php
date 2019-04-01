<?php
namespace Api\Modules\Api\Controllers;


use Api\Models\GalaxyAdmin;
use express\express;
use Phalcon\Mvc\Controller;
use Api\Services\TestServer;
use Phalcon\Mvc\Model;

use App\Utils\Contract\LogInteface;

use App\Utils\Contract;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/15
 * Time: 14:19
 */
class TestController extends Controller{

    public function testsAction(){
        // echo "asd";die;
        //设置顾问的名称
        $applyType = 'test';
        $this->wechatwork->accesstoken($applyType);
        pr($this->wechatwork->send());
        // pr($this->wechatwork->send());

    }

    public function testssAction(){
    	$this->wechatwork->simplelistGet(1,1);
    }

//     public function get(){
// //        $a = new \Redis();
// //        var_dump(get_class_methods($a));exit;
//         $a = $this->di->get('redis');
// //        $obj->get("123");exit;
// //        var_dump($a);exit;
//         $a::save("ttt123:qqqq",123,50);
//         $b = $a::get("ttt123:qqqq");
//         var_dump($b);exit;
//         $a->end();exit;
//     }
//     public function testAction()
//     {
//         $express = new express();
//         $param['com'] = 'yuantong';
//         $param['num'] = '500306190180';
// //        $param['from'] = '广东省深圳市';
// //        $param['to'] = '北京市朝阳区';
//         $express->getExpress($param);
//         exit;
//         $a = GalaxyAdmin::find(20);
// //        print_r($a->toArray());
//         exit;
//         $redis = $this->redis;
// //        $redis::sAdd("test123","4");exit;
//         $a = $redis::sscan('test123',4,"666");
//         var_dump($a);

//         exit;
//         $a = $this->SmsHelper;
//         var_dump($a);exit;
//         var_dump($a);exit;
//          $a::save("213123","111222333");
//         $b = $a->exists("213123");
//         var_dump($b);exit;

//         $b = $a->get("213123");
// //        $a = $this->di->get('Log');
// //        $b = $a->log("infos","测试321123",array("error"=>true));
//         $arr = [
//             ['message' => '1111', 'type' => true],
//             ['message' => '2222', 'type' => false],
//             ['message' => '3333', 'type' => true],
//         ];
//         $b = $a->transactionLog("redis", $arr);
//         exit;
//         $a::begin();
//         $a->alert("This is an alert");
//         $a->rollback();
// //        $b = $a->log("infos","测试321123",array("error"=>true));
// //        $b = $a->log("","123321123",array("error"=>false));
//     }
//     public function onConstruct(){
//         $this->url = $this->di->get('config')->apiUrl;
//         $this->testServer = new TestServer();
//     }

//     public function getTestAction(){
//         $urls = $this->testServer->getTestList();
//     }

    
}