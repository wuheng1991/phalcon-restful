<?php
// +----------------------------------------------------------------------
// | Cron定时服务脚本 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
namespace App\Tasks\System;

use Phalcon\Cli\Task;

class CronTask extends Task
{
    public $description = '定时器执行脚本';

    public $tasks = [
    ];

    public function mainAction()
    {
        if (!class_exists(Schedule::class)) {
            echo Color::colorize("-------------------------------------------", Color::FG_LIGHT_GREEN) . PHP_EOL;
            echo Color::colorize("           Schedule 支持库不存在！           ", Color::FG_LIGHT_GREEN) . PHP_EOL;
            echo Color::colorize("-------------------------------------------", Color::FG_LIGHT_GREEN) . PHP_EOL;
            echo Color::head("请使用以下命令安装：") . PHP_EOL;
            echo Color::colorize("") . PHP_EOL;
            return;
        }

        $tasks = $this->tasks;
        $schedule = new Schedule();
        foreach ($tasks as $task) {
            list($func, $params) = $task['schedule'];
            unset($task['schedule']);
            if ($schedule->$func(...$params)) {
                $this->logInfo(json_encode($task));
                $this->console->handle($task);
            }
        }
    }

    /**
     * @desc   保存日志
     * @param $msg
     */
    protected function logInfo($msg)
    {
        $factory = di('logger');
        $logger = $factory->getLogger('cron', Sys::LOG_ADAPTER_FILE);
        $logger->info($msg);
        echo Color::success($msg);
    }
}
