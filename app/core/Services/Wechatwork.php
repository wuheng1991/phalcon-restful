<?php
// +----------------------------------------------------------------------
// | Wechat 服务 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services;

use Phalcon\Config;
use Phalcon\DI\FactoryDefault;

class Wechatwork implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        $di->setShared("wechatwork",function(){
            return new \App\Utils\Wechatwork();
        });
    }
}