<?php
// +----------------------------------------------------------------------
// | Redis 服务 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services;

use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use Phalcon\Cache\Backend\Redis as RedisConnect;
use Phalcon\Cache\Frontend\Data as FrontData;
use App\Utils;

class Redis implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {

        $di->set('redis', function () use ($config) {
           return  Utils\Redis::getInstance();
//            $frontCache = new FrontData(["lifetime" => $config->redis->lifetime,]);
//            $cache = new RedisConnect(
//                $frontCache,
//                [
//                    'host' => $config->redis->host,
//                    'port' => $config->redis->port,
//                    'auth' => $config->redis->auth,
//                    'persistent' => $config->redis->persistent,
//                    'index'      => $config->redis->index,//設置redis存放的表
////                    'prefix' => $config->redis->prefix,
//                ]
//            );
//            return $cache;
        });
    }
}