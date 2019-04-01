<?php

namespace Api\Modules\Backend;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Config;
use Phalcon\Logger\Adapter\File as FileLogger;

defined('DEFAULT_MODULE') || define('DEFAULT_MODULE', 'backend');

class Module implements ModuleDefinitionInterface
{
    /**
     * Registers an autoloader related to the module
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces([
            'Api\Modules\Backend\Controllers' => __DIR__ . '/controllers/',
            'Api\Modules\Backend\Models'      => __DIR__ . '/models/'
        ]);

        $loader->register();
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        /**
         * Try to load local configuration
         */
        if (file_exists(__DIR__ . '/config/config.php')) {
            
            $config = $di['config'];
            
            $override = new Config(include __DIR__ . '/config/config.php');

            if ($config instanceof Config) {
                $config->merge($override);
            } else {
                $config = $override;
            }
        }

        /**
         * Setting up the view component
         */
        $di['view'] = function () {
            $config = $this->getConfig();

            $view = new View();
            $view->setViewsDir($config->get('application')->viewsDir);
            
            $view->registerEngines([
                '.volt'  => 'voltShared',
                '.phtml' => PhpEngine::class
            ]);

            return $view;
        };


        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
//        $di['db'] = function () use ($di){
//            $config = $this->getConfig();
//
//            # 记录sql日志
//            $db_log = APP_PATH.'/modules/backend/runtime/logs/db.log';
//            if(!file_exists($db_log)){
//                fopen($db_log, "a+");
//                //mkdir($task_log,0777, true);
//            }
//
//            $this->logger = new FileLogger($db_log);
//
//            $eventsManager = new \Phalcon\Events\Manager();
//            $profiler = $di->getProfiler();
//            //监听所有的db事件
//            $eventsManager->attach('db', function($event, $connection) use ($profiler) {
//                //一条语句查询之前事件，profiler开始记录sql语句
//                if ($event->getType() == 'beforeQuery') {
//                    $profiler->startProfile($connection->getSQLStatement());
//                    $this->logger->log(\Phalcon\Logger::INFO, $connection->getSQLStatement());
//                }
//                //一条语句查询结束，结束本次记录，记录结果会保存在profiler对象中
//                if ($event->getType() == 'afterQuery') {
//                    $profiler->stopProfile();
//
//                }
//            });
//
//
//            $dbConfig = $config->database->toArray();
//
//            $dbAdapter = '\Phalcon\Db\Adapter\Pdo\\' . $dbConfig['adapter'];
//            unset($config['adapter']);
//
//            #return new $dbAdapter($dbConfig);
//            $connection = new $dbAdapter($dbConfig);
//            $connection->setEventsManager($eventsManager);
//            return $connection;
//        };
    }
}
