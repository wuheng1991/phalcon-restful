<?php
// +----------------------------------------------------------------------
// | Redis 服务 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services;

use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use Phalcon\Cache\Frontend\Data as FrontData;
use App\Utils;

class Mail implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {

        $di->set('mail', function () use ($config) {
           	return new Utils\Mail();
        });
    }
}