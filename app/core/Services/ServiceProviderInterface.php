<?php
// +----------------------------------------------------------------------
// | 服务依赖接口 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services;

use Phalcon\DI\FactoryDefault;
use Phalcon\Config;

interface ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config);
}