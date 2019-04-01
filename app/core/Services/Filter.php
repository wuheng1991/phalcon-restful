<?php
// +----------------------------------------------------------------------
// | Filter 服务 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services;

use Phalcon\Config;
use Phalcon\DI\FactoryDefault;

class Filter implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        /**
         * Phalcon\Filter
         */
        $di->setShared('filter', function () {
            return new \Phalcon\Filter();
        });
    }

}