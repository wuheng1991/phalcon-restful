<?php
// +----------------------------------------------------------------------
// | DB 服务 [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Services;

use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\DI\FactoryDefault;
use Phalcon\Config;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger\Adapter\File as FileAdapter;
use App\Core\Event\DbListener;
use PDO;

class Db implements ServiceProviderInterface
{
    public function register(FactoryDefault $di, Config $config)
    {
        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
        $di->setShared('db', function () use ($config,$di) {
            $Dblistener = new DbListener();
            $eventsManager = $Dblistener->logSql();
            $dbConfig = $config->database->toArray();
            $dbAdapter = '\Phalcon\Db\Adapter\Pdo\\' . $dbConfig['adapter'];
            unset($config['adapter']);
            $connection = new $dbAdapter($dbConfig);
            $connection->setEventsManager($eventsManager);
            return $connection;
        });
    }

}