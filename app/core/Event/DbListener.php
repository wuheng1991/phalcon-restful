<?php
// +----------------------------------------------------------------------
// | 数据库操作 LISTENER [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Event;

use Phalcon\Db\Profiler;
use Phalcon\Events\Event;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

class DbListener
{
    const TIMEOUT = 0.1;
    protected $_profiler;

    protected $_logger;

    /**
     *创建分析器并开始纪录
     */
    public function __construct()
    {
        $config = \Phalcon\Di::getDefault()->getShared('config');
        $dir = $config->application->logDir . date('Ymd');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $this->_profiler = new Profiler();

        /** @var Factory $factory */
        $factory = \Phalcon\Di::getDefault()->getShared('logger');
//        $this->_logger = $factory->getLogger('sql', Sys::LOG_ADAPTER_FILE);
    }

    /**
     * 如果事件触发器是'beforeQuery'，此函数将会被执行
     */
    public function beforeQuery(Event $event, $connection)
    {
        $this->_profiler->startProfile(
            $connection->getSQLStatement(),
            $connection->getSqlVariables()
        );
    }

    /**
     * 如果事件触发器是'afterQuery'，此函数将会被执行
     */
    public function afterQuery(Event $event, $connection)
    {
        $this->_profiler->stopProfile();
        // 保存sql语句以及执行时间
        $this->logSql();
    }

    public function getProfiler()
    {
        return $this->_profiler;
    }

    /**
     * @desc   记录sql执行日志
     * @author limx
     */
    public function logSql()
    {
        global $di;
        $eventsManager = new \Phalcon\Events\Manager();
        $profiler = $di->getProfiler();
//      //监听所有的db事件
        $eventsManager->attach('db', function($event, $connection, $profiler) use
        ($profiler,$di) {

            //一条语句查询之前事件，profiler开始记录sql语句
            if ($event->getType() == 'beforeQuery') {
                $profiler->startProfile($connection->getSQLStatement());
            }
            //一条语句查询结束，结束本次记录，记录结果会保存在profiler对象中
            if ($event->getType() == 'afterQuery') {
                $profiler->stopProfile();

                $dir = dirname(dirname(dirname(__FILE__)))."/logs/".DEFAULT_MODULE.'/'.date("Ymd",time());
                if (!file_exists($dir)){
                    mkdir ($dir,0777,true);
                }

                $db_log = $dir.'/db.log';
                if(!file_exists($db_log)){
                    fopen($db_log, "a+");
                }

                $this->logger = new FileAdapter($db_log);
                $this->logger->begin();

                $profiles = $profiler->getProfiles();
                foreach ($profiles as $profile) {
                    $this->logger->info("执行日期: ".date("Y-m-d H:i:s",time()));
                    $this->logger->info("开始时间: ".$profile->getInitialTime());
                    $this->logger->info("结束时间: ".$profile->getFinalTime());
                    $this->logger->info("消耗时间: ".$profile->getTotalElapsedSeconds());
                    $this->logger->info("sql语句: ".$profile->getsqlStatement()."\n");

                }
                $this->logger->commit();
            }
        });
        return $eventsManager;
    }

}