<?php

namespace Api\Services;

use Api\Models\GalaxyToken;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 14:43
 */
class TestServer extends BaseServer
{

    //获取活动列表
    public function getTestList(){
        $galaxyToken = new GalaxyToken();
        $token = $galaxyToken->test();
        // pr($token);
        // $token->id = "asd";
        // $token->content = "asd";
        // $token->posttime = "asd";
        // if($token->save() == false){
        //     foreach ($token->getMessages() as $message) {
        //         echo "Message: ".$message->getMessage().PHP_EOL;
        //         echo "Field: ", $message->getField().PHP_EOL;
        //         echo "Type: ", $message->getType();
        //     }
        // }
        pr($token->toArray());
    }
}