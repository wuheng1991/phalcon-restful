<?php
// +----------------------------------------------------------------------
// | Crypt 服务 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services;

use Phalcon\Config;
use Phalcon\DI\FactoryDefault;

class Crypt implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        $di->setShared(
            "crypt",
            function () use ($config) {
                $crypt = new \Phalcon\Crypt();

                $crypt->setKey($config->crypt->key); // 使用你自己的key！

                return $crypt;
            }
        );
    }

}