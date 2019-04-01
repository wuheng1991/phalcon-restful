<?php
// +----------------------------------------------------------------------
// | Dispatcher 服务 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services\Cli;

use App\Core\Services\ServiceProviderInterface;
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use Phalcon\Events\Manager;
use App\Core\Event\Cli\DispatchListener;
use Phalcon\Cli\Dispatcher AS CliDispatcher;

class Dispatcher implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        /**
         * Set the default namespace for dispatcher
         */
//        $di->setShared('dispatcher', function () {
//
//            // 监听调度 dispatcher
//            $eventsManager = new Manager();
//            $dispatchListener = new DispatchListener();
//            $eventsManager->attach(
//                'dispatch',
//                $dispatchListener
//            );
//
//            $dispatcher = new \Phalcon\Cli\Dispatcher();
//            $dispatcher->setDefaultNamespace('App\\Tasks');
//            // 分配事件管理器到分发器
//            $dispatcher->setEventsManager($eventsManager);
//
//            return $dispatcher;
//        });
        $di->setShared('dispatcher', function() {
            $dispatcher = new CliDispatcher();
            $dispatcher->setDefaultNamespace('Api\Modules\Cli\Tasks');
            return $dispatcher;
        });
    }
}