<?php
// +----------------------------------------------------------------------
// | modelsManager 服务 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services;

use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Model\Manager;

class ModelsManager implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        $di->setShared('modelsManager', function () {
            return new Manager();
        });
    }

}