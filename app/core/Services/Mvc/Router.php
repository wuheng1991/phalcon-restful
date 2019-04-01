<?php
// +----------------------------------------------------------------------
// | Router 服务 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services\Mvc;

use App\Core\Services\ServiceProviderInterface;
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;

class Router implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        $di->setShared('router', function () use ($config) {
            $router = new \Phalcon\Mvc\Router(false);
            $dir = $config->application->configDir . 'routes';
            foreach (glob($dir . '/*.php') as $item) {
                include_once $item;
            }
            return $router;
        });
    }

}