<?php
// +----------------------------------------------------------------------
// | 日志工具类 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
namespace App\Utils;
use App\Utils\Contract\LogInteface;
use Phalcon\Logger\Adapter\File as FileAdapter;

class Log implements LogInteface
{
    /** 日志目录 */
    const DIR = "/";
    protected $dir;
    /** 文件名 */
    const FILE_NAME = "info";
    protected $file_name;
    /** 日志类型 */
    const TYPE = "log";
    protected $type;

    /**
     * @desc   获取日志类实例
     * @author limx
     * @return mixed
     */
    protected static function logger($type=self::FILE_NAME)
    {
         $dir = dirname(dirname(__FILE__))."/logs/".DEFAULT_MODULE.'/'.date("Ymd",time());
         if (!file_exists($dir)){
            mkdir ($dir,0777,true);
         }
         $file = static::DIR.$type.'.'.static::TYPE;
         $logger = new FileAdapter($dir.$file);
         return self::getLogger($logger,$type,static::TYPE,static::DIR);
    }

    public static function getLogger($logger,$FILE_NAME, $TYPE, $DIR){
        $logger->dir = $DIR;
        $logger->file_name = $FILE_NAME;
        $logger->type = $TYPE;
        return $logger;
    }

    /**
     * 日志普通写入
     * @desc   Sends/Writes messages to the file log
     * @author limx
     * @param  mixed      $type
     * @param  mixed      $message
     * @param  array|null $context
     * @return mixed
     */
    public static function log($filename, $message = null, array $context = null)
    {
        $logger = static::logger($filename);
        if($context['error']){
             $logger->log($message,\Phalcon\Logger::ERROR);
        }else{
             $logger->log($message);
        }
    }

    /**
     * 日志事务写入
     * @param $filename
     * @param array|null $content
     * $content = ['message'=>$content,'type'=>true/false]
     */
    public static function transactionLog($filename,array $content = null){
        $logger = static::logger($filename);
        $logger->begin();
        if(empty($content)){
            return;
        }
        foreach($content as $k=>$v){
            if($v['type'] == false){
                $logger->error($v['message']);
            }else{
                $logger->alert($v['message']);
            }
        }
        $logger->commit();
    }
    /**
     * @desc   Starts a transaction
     * @author limx
     * @return mixed
     */
    public static function begin()
    {

        $logger = static::logger();
        $logger->begin();
    }

    /**
     * @desc   Commits the internal transaction
     * @author limx
     * @return mixed
     */
    public static function commit()
    {
        $logger = static::logger();
        $logger->commit();
    }

    /**
     * @desc   Rollbacks the internal transaction
     * @author limx
     * @return mixed
     */
    public static function rollback()
    {
        $logger = static::logger();
        $logger->rollback();
    }

    /**
     * @desc   Sends/Writes a debug message to the log
     * @author limx
     * @param  string $message
     * @param  array  $context
     * @return mixed
     */
    public static function debug($message, array $context = null)
    {
        $logger = static::logger();
        return $logger->debug($message, $context);
    }

    /**
     * @desc   Sends/Writes an error message to the log
     * @author limx
     * @param  string $message
     * @param  array  $context
     * @return mixed
     */
    public static function error($message, array $context = null)
    {
        $logger = static::logger();
        return $logger->error($message, $context);
    }

    /**
     * @desc   Sends/Writes an info message to the log
     * @author limx
     * @param  string $message
     * @param  array  $context
     * @return mixed
     */
    public static function info($message, array $context = null)
    {
        $logger = static::logger();
        return $logger->info($message, $context);
    }

    /**
     * @desc   Sends/Writes a notice message to the log
     * @author limx
     * @param  string     $message
     * @param  array|null $context
     * @return mixed
     */
    public static function notice($message, array $context = null)
    {
        $logger = static::logger();
        return $logger->notice($message, $context);
    }

    /**
     * @desc   Sends/Writes a warning message to the log
     * @author limx
     * @param  string     $message
     * @param  array|null $context
     * @return mixed
     */
    public static function warning($message, array $context = null)
    {
        $logger = static::logger();
        return $logger->warning($message, $context);
    }

    /**
     * @desc   Sends/Writes an alert message to the log
     * @author limx
     * @param  string     $message
     * @param  array|null $context
     * @return mixed
     */
    public static function alert($message, array $context = null)
    {
        $logger = static::logger();
        return $logger->alert($message, $context);
    }

    /**
     * @desc   Sends/Writes an emergency message to the log
     * @author limx
     * @param  string     $message
     * @param  array|null $context
     * @return mixed
     */
    public static function emergency($message, array $context = null)
    {
        $logger = static::logger();
        return $logger->emergency($message, $context);
    }
}
