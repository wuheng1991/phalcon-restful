<?php
// +----------------------------------------------------------------------
// | SmsHelper 服务 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services;

use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use SmsHelper\SmsHelper as SmsHelpers;

class Smshelper implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        $di->setShared("SmsHelper",function(){
            return new \SmsHelper\SmsHelpers();
        });
    }
}