<?php
// +----------------------------------------------------------------------
// | Dispatcher 服务 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services\Mvc;

use App\Core\Services\ServiceProviderInterface;
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use Phalcon\Events\Manager;
use App\Core\Event\Mvc\DispatchListener;
use App\Core\Event\RouterListener;

class Dispatcher implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        $di->setShared('dispatcher', function () use ($config) {
            // 监听调度 dispatcher
            $eventsManager = new Manager();
            $dispatchListener = new RouterListener();

            $eventsManager->attach(
                'dispatch',
                $dispatchListener
            );

            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            $dispatcher->setDefaultNamespace('App\Controllers');
            // 分配事件管理器到分发器
            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });
    }

}