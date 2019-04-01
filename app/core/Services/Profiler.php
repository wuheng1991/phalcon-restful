<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/17
 * Time: 14:30
 */
namespace App\Core\Services;

use Phalcon\Config;
use Phalcon\DI\FactoryDefault;

class Profiler implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        /**
         * Phalcon\Filter
         */
        $di->set('profiler', function(){
            return
                new  \Phalcon\Db\Profiler();
        }, true);
    }
}