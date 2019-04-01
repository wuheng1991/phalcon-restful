<?php

namespace Api\Modules\Api;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Config;

defined('DEFAULT_MODULE') || define('DEFAULT_MODULE', 'api');

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
            'Api\Modules\Api\Controllers' => __DIR__ . '/controllers/',
            'Api\Modules\Api\Models'      => __DIR__ . '/models/'
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
//        $di['db'] = function () use($di){
//            $config = $this->getConfig();
//
//            $eventsManager = new \Phalcon\Events\Manager();
//            $profiler = $di->getProfiler();
//            //监听所有的db事件
//            $eventsManager->attach('db', function($event, $connection) use
//            ($profiler) {
//                //一条语句查询之前事件，profiler开始记录sql语句
//                if ($event->getType() == 'beforeQuery') {
//                    $profiler->startProfile($connection->getSQLStatement());
//                }
//                //一条语句查询结束，结束本次记录，记录结果会保存在profiler对象中
//                if ($event->getType() == 'afterQuery') {
//                    $profiler->stopProfile();
//                }
//            });
//
//            $dbConfig = $config->database->toArray();
//
//            $dbAdapter = '\Phalcon\Db\Adapter\Pdo\\' . $dbConfig['adapter'];
//
//            unset($config['adapter']);
//
//            $connection = new $dbAdapter($dbConfig);
//            $connection->setEventsManager($eventsManager);
//            return $connection;
//
//        };
    }
}
