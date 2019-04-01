<?php
// +----------------------------------------------------------------------
// | Error 捕获服务 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services;

use App\Core\Exception\HandleExceptions;
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;

class Error implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        if ($config->log->error) {
            $handler = new HandleExceptions();
            $handler->bootstrap($di);
        }
    }

}