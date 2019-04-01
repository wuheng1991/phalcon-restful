<?php
// +----------------------------------------------------------------------
// | XConsole.php [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services\Cli;

use App\Core\Services\ServiceProviderInterface;
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use Xin\Phalcon\Cli\XConsole as XConsoleApp;

class XConsole implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        $di->setShared('xconsole', function () use ($di) {
            return new XConsoleApp($di);
        });
    }

}