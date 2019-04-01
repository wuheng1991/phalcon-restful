<?php
// +----------------------------------------------------------------------
// | Logger.php [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services;

use App\Utils\Log;
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;

class Logger implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        /**
         *  $factory = di('logger');
         *  $logger = $factory->getLogger('info', Sys::LOG_ADAPTER_FILE, ['dir' => 'system']);
         *  $logger->info('日志测试');
         */
        $di->setShared('logger', function () use ($config) {
           return new Log();
//           return new FactoryDefault();
        });
    }

}