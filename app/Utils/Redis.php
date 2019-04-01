<?php
// +----------------------------------------------------------------------
// | Redis工具类 [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
namespace App\Utils;
//use Phalcon\Cache\Backend\Redis as PRedis;
use Phalcon\Cache\Frontend\Data as FrontData;

class Redis
{
    static public $index = 0;
    public static function getInstance(){
        return new Redis();
    }

    public static function init(){
        global $config;
        $redis = new \Redis();
        $redis->connect($config->redis->host,$config->redis->port);
        $redis->auth($config->redis->auth);
        $index = $config->redis->index;
        if(!empty(self::$index)){
            $index = self::$index;
        }
        $redis->select($index);
        return $redis;
    }

    public  function setIndex($indexa){
        self::$index = $indexa;
        return $this;
    }
/* 
    public static function __callStatic($name, $arguments)
    {
        $redis = di('redis');
        return call_user_func_array([$redis, $name], $arguments);
    } */

    /**
     * @desc   因为Redis工具类使用的是一个redis实例
     *         所以当我们在某个区块修改了redis的db，其他也会受到影响。所以这里增加一个辅助函数。
     *         可以让redis的db更改为配置里的db。
     * @author limx
     */
  /*   public static function end()
    {
        $redis = di('redis');
        $db = di('config')->redis->index;
        return $redis->select($db);
    } */

    //String操作开始
    /**
     * 设置键值：成功返回true，否则返回false
     * @param $key
     * @param $val
     * @param int $expire
     * @return bool
     */
    public static function save($key,$val,$expire=7200){
        $redis = self::init();
        return $redis->set($key,$val,$expire);
    }


    /**
     * 删除键
     * @param $key
     * @return int
     */
    public static function delete($key){
        $redis = self::init();
        return $redis->delete($key);
    }

    /**
     * 获取键值：成功返回String类型键值，若key不存在或不是String类型则返回false
     * @param $key
     * @return bool|string
     */
    public static function get($key){
        $redis = self::init();
        return $redis->get($key);
    }

    /**
     * 判断key是否存在
     * @param $key
     * @return bool
     */
    public static function exists($key){
        $redis = self::init();
        return $redis->exists($key);
    }

    /**
     * 从某个key所存储的字符串的指定偏移量开始，替换为另一指定字符串，成功返回替换后新字符串的长度
     * @param $key
     * @param $offset
     * @param $replace
     * @return string
     */
    public static function setRange($key,$offset,$replace){
        $redis = self::init();
        return $redis->setRange($key,$offset,$replace);
    }

    /**
     * 获取存储在指定key中字符串的子字符串。
     * @param $key
     * @param $start
     * @param $end
     * @return string
     */
    public static function getRange($key,$start,$end){
        $redis = self::init();
        return $redis->getRange($key,$start,$end);
    }

    /**
     * 设置新值，返回旧值：若key不存在则设置值，返回false
     * @param $key
     * @param $val
     * @param int $expire
     * @return string
     */
    public static function getSet($key,$val,$expire=7200){
        $redis = self::init();
        return $redis->getSet($key,$val,$expire);
    }

    /**
     * 一次设置多个键值对：成功返回true。
     * @param $arr
     * @return bool
     */
    public static function mset($arr){
        $redis = self::init();
        return $redis->mset($arr);
    }

    /**
     * 一次获取多个key的值：返回一个键值对数组，其中不存在的key值为false
     * @param $arr
     * @return array
     */
    public static function  mget($arr){
        $redis = self::init();
        return $redis->mget($arr);
    }

    /**
     * key的值不存在时，才为其设置值。key不存在且设置成功返回true，否则返回false
     * @param $key
     * @param $val
     * @return bool
     */
    public static function  setnx($key,$val){
        $redis = self::init();
        return $redis->setnx($key,$val);
    }

    /**
     * setnx命令的批量操作。只有在给定所有key都不存在的时候才能设置成功，只要其中一个key存在，所有key都无法设置成功
     * @param $key
     * @param $val
     * @return int
     */
    public static function  msetnx($arr){
        $redis = self::init();
        return $redis->msetnx($arr);
    }

    /**
     * 获取指定key存储的字符串的长度，key不存在返回0，不为字符串返回false
     * @param $key
     * @return int
     */
    public static function  strlen($key){
        $redis = self::init();
        return $redis->strlen($key);
    }

    /**
     * 将指定key存储的数字值增加1。若key不存在会先初始化为0再增加1，若key存储的不是整数值则返回false。成功返回key新值
     * @param $key
     * @return int
     */
    public static function  incr($key){
        $redis = self::init();
        return $redis->incr($key);
    }

    /**
     * 给指定key存储的数字值增加指定增量值
     * @param $key
     * @param $num
     * @return int
     */
    public static function  incrBy($key,$num){
        $redis = self::init();
        return $redis->incrBy($key,$num);
    }

    /**
     * 给指定key存储的数字值增加指定浮点数增量
     * @param $key
     * @param $num
     * @return float
     */
    public static function  incrByFloat($key,$num){
        $redis = self::init();
        return $redis->incrByFloat($key,$num);
    }

    /**
     * 将指定key存储的数字值减一
     * @param $key
     * @return int
     */
    public static function  decr($key){
        $redis = self::init();
        return $redis->decr($key);
    }

    /**
     * 将指定key存储的数字值减去指定减量值
     * @param $key
     * @param $num
     * @return int
     */
    public static function  decrBy($key,$num){
        $redis = self::init();
        return $redis->decrBy($key,$num);
    }

    /**
     * 为指定key追加值到原值末尾，若key不存在则相对于set()函数
     * @param $key
     * @param $val
     * @return int
     */
    public static function  append($key,$val){
        $redis = self::init();
        return $redis->append($key,$val);
    }

    //String操作结束

    //Hash操作开始
    /**
     * 为hash表中的字段赋值。成功返回1，失败返回0。若hash表不存在会先创建表再赋值，若字段已存在会覆盖旧值
     * @param $key
     * @return int
     */
    public static function  hSet( $key, $hashKey, $value){
        $redis = self::init();
        return $redis->hSet( $key, $hashKey, $value);
    }

    /**
     * 获取hash表中指定字段的值。若hash表不存在则返回false
     * @param $key
     * @param $hashKey
     * @return string
     */
    public static function  hGet( $key, $hashKey){
        $redis = self::init();
        return $redis->hGet( $key, $hashKey);
    }

    /**
     * 查看hash表的某个字段是否存在，存在返回true，否则返回false
     * @param $key
     * @param $hashKey
     * @return bool
     */
    public static function  hExists( $key, $hashKey){
        $redis = self::init();
        return $redis->hExists( $key, $hashKey);
    }

    /**
     * 删除hash表的一个字段，不支持删除多个字段。成功返回1，否则返回0
     * @param $key
     * @param $hashKey
     * @return int
     */
    public static function  hDel( $key, $hashKey){
        $redis = self::init();
        return $redis->hDel( $key, $hashKey);
    }

    /**
     * 同时设置某个hash表的多个字段值。成功返回true
     * @param $key
     * @param $hashKey_arr
     * @return bool
     */
    public static function  hMset( $key, $hashKey_arr){
        $redis = self::init();
        return $redis->hMset( $key, $hashKey_arr);
    }

    /**
     * 同时获取某个hash表的多个字段值。其中不存在的字段值为false
     * @param $key
     * @param $hashKey_arr
     * @return array
     */
    public static function  hMget( $key, $hashKey_arr){
        $redis = self::init();
        return $redis->hMget( $key, $hashKey_arr);
    }

    /**
     * 获取某个hash表所有的字段和值
     * @param $key
     * @return array
     */
    public static function  hGetAll( $key){
        $redis = self::init();
        return $redis->hGetAll( $key);
    }

    /**
     * 获取某个hash表所有字段名。hash表不存在时返回空数组，key不为hash表时返回false
     * @param $key
     * @return array
     */
    public static function  hKeys( $key){
        $redis = self::init();
        return $redis->hKeys( $key);
    }

    /**
     * 获取某个hash表所有字段值
     * @param $key
     * @return array
     */
    public static function  hVals( $key){
        $redis = self::init();
        return $redis->hVals( $key);
    }

    /**
     * 为hash表中不存在的字段赋值。若hash表不存在则先创建，若字段已存在则不做任何操作。设置成功返回true，否则返回false
     * @param $key
     * @param $hashKey
     * @param $value
     * @return bool
     */
    public static function  hSetNx( $key, $hashKey, $value){
        $redis = self::init();
        return $redis->hSetNx( $key, $hashKey, $value);
    }

    /**
     * 获取某个hash表的字段数量。若hash表不存在返回0，若key不为hash表则返回false
     * @param $key
     * @return int
     */
    public static function  hLen( $key){
        $redis = self::init();
        return $redis->hLen( $key);
    }

    /**
     * 为hash表中的指定字段加上指定增量值，若增量值为负数则相当于减法操作。若hash表不存在则先创建，若字段不存在则先初始化值为0再进行操作，若字段值为字符串则返回false。设置成功返回字段新值
     * @param $key
     * @param $hashKey
     * @param $value
     * @return int
     */
    public static function  hIncrBy(  $key, $hashKey, $value ){
        $redis = self::init();
        return $redis->hIncrBy(  $key, $hashKey, $value );
    }

    /**
     * 为hash表中的指定字段加上指定浮点数增量值
     * @param $key
     * @param $field
     * @param $increment
     * @return float
     */
    public static function  hIncrByFloat(  $key, $field, $increment ){
        $redis = self::init();
        return $redis->hIncrByFloat(  $key, $field, $increment );
    }

    //Hash操作结束

    //List操作开始

    /**
     * 从list头部插入一个值
     * @param $key
     * @param $value1
     * @return int
     */
    public static function  lPush( $key,$value1){
        $redis = self::init();
        return $redis->lPush($key,$value1);
    }

    /**
     * 从list尾部插入一个值
     * @param $key
     * @param $value1
     * @return int
     */
    public static function  rPush( $key,$value1){
        $redis = self::init();
        return $redis->rPush($key,$value1);
    }

    /**
     * 获取列表指定区间中的元素。0表示列表第一个元素，-1表示最后一个元素，-2表示倒数第二个元素
     * @param $key
     * @param $start
     * @param $end
     * @return array
     */
    public static function  lrange( $key, $start, $end ){
        $redis = self::init();
        return $redis->lrange( $key, $start, $end );
    }

    /**
     * 将一个插入已存在的列表头部，列表不存在时操作无效
     * @param $key
     * @param $value
     * @return int
     */
    public static function  lPushx( $key, $value ){
        $redis = self::init();
        return $redis->lPushx( $key, $value);
    }

    /**
     * 将一个或多个值插入已存在的列表尾部，列表不存在时操作无效
     * @param $key
     * @param $value
     * @return int
     */
    public static function  rPushx( $key, $value ){
        $redis = self::init();
        return $redis->rPushx( $key, $value);
    }

    /**
     * 移除并返回列表的第一个元素，若key不存在或不是列表则返回false
     * @param $key
     * @return string
     */
    public static function  lPop( $key){
        $redis = self::init();
        return $redis->lPop( $key);
    }

    /**
     * 移除并返回列表的最后一个元素，若key不存在或不是列表则返回false
     * @param $key
     * @return string
     */
    public static function  rPop( $key){
        $redis = self::init();
        return $redis->rPop( $key);
    }

    /**
     * 移除并获取列表的第一个元素。如果列表没有元素则会阻塞列表直到等待超时或发现可弹出元素为止。
     * 参数：key，超时时间（单位：秒）
     * 返回值：[0=>key,1=>value]，超时返回[]
     * @param $key
     * @return array
     */
    public static function  blPop($keys, $timeout){
        $redis = self::init();
        return $redis->blPop($keys, $timeout);
    }

    /**
     * 移除并获取列表的最后一个元素。如果列表没有元素则会阻塞列表直到等待超时或发现可弹出元素为止。
     * 参数：key，超时时间（单位：秒）
     * 返回值：[0=>key,1=>value]，超时返回[]
     * @param $keys
     * @param $timeout
     * @return array
     */
    public static function  brPop($keys, $timeout){
        $redis = self::init();
        return $redis->brPop($keys, $timeout);
    }

    /**
     * 移除列表中最后一个元素，将其插入另一个列表头部，并返回这个元素。若源列表没有元素则返回false
     * @param $srcKey
     * @param $dstKey
     * @return string
     */
    public static function  rpoplpush($srcKey, $dstKey){
        $redis = self::init();
        return $redis->rpoplpush($srcKey, $dstKey);
    }

    /**
     * 移除列表中最后一个元素，将其插入另一个列表头部，并返回这个元素。如果列表没有元素则会阻塞列表直到等待超时或发现可弹出元素为止。
     *  参数：源列表，目标列表，超时时间（单位：秒）
     * 超时返回false
     * @param $srcKey
     * @param $dstKey
     * @param $timeout
     * @return string
     */
    public static function  brpoplpush($srcKey, $dstKey, $timeout){
        $redis = self::init();
        return $redis->brpoplpush($srcKey, $dstKey, $timeout);
    }

    /**
     * 返回列表长度
     * @param $Key
     * @return int
     */
    public static function  lLen($Key){
        $redis = self::init();
        return $redis->lLen($Key);
    }

    /**
     * 通过索引获取列表中的元素。若索引超出列表范围则返回false
     * @param $key
     * @param $index
     * @return String
     */
    public static function  lindex($key, $index){
        $redis = self::init();
        return $redis->lindex($key, $index);
    }

    /**
     * 通过索引设置列表中元素的值。若是索引超出范围，或对一个空列表进行lset操作，则返回false
     * @param $key
     * @param $index
     * @param $value
     * @return bool
     */
    public static function  lSet( $key, $index, $value ){
        $redis = self::init();
        return $redis->lSet( $key, $index, $value );
    }

    /**
     * 在列表中指定元素前或后面插入元素。若指定元素不在列表中，或列表不存在时，不执行任何操作。
     * 参数：列表key，Redis::AFTER或Redis::BEFORE，基准元素，插入元素
     * 返回值：插入成功返回插入后列表元素个数，若基准元素不存在返回-1，若key不存在返回0，若key不是列表返回false。
     * @param $key
     * @param $position
     * @param $pivot
     * @param $value
     * @return int
     */
    public static function  lInsert($key, $position, $pivot, $value){
        $redis = self::init();
        return $redis->lInsert($key, $position, $pivot, $value);
    }

    /**
     * 根据第三个参数count的值，移除列表中与参数value相等的元素。
     * count > 0 : 从表头开始向表尾搜索，移除与value相等的元素，数量为count。
     * count < 0 : 从表尾开始向表头搜索，移除与value相等的元素，数量为count的绝对值。
     * count = 0 : 移除表中所有与value相等的值。
     * 返回实际删除元素个数
     * @param $key
     * @param $value
     * @param $count
     * @return int
     */
    public static function  lrem($key, $value, $count){
        $redis = self::init();
        return $redis->lrem($key, $value, $count);
    }

    /**
     * 对一个列表进行修剪，只保留指定区间的元素，其他元素都删除。成功返回true
     * @param $key
     * @param $value
     * @param $count
     * @return array
     */
    public static function  ltrim($key, $start, $stop){
        $redis = self::init();
        return $redis->ltrim($key, $start, $stop);
    }

    //List操作结束

    //Set操作开始

    /**
     * 将一个元素加入集合，已经存在集合中的元素则忽略。若集合不存在则先创建，若key不是集合类型则返回false，若元素已存在返回0，插入成功返回1
     * @param $key
     * @param $value
     * @return int
     */
    public static function  sAdd($key, $value){
        $redis = self::init();
        return $redis->sAdd($key, $value);
    }

    /**
     * 返回集合中所有成员
     * @param $key
     * @return array
     */
    public static function  sMembers($key){
        $redis = self::init();
        return $redis->sMembers($key);
    }

    /**
     * 判断指定元素是否是指定集合的成员，是返回true，否则返回false
     * @param $key
     * @return bool
     */
    public static function  sismember($key,$value){
        $redis = self::init();
        return $redis->sismember($key,$value);
    }

    /**
     * 返回集合中元素的数量
     * @param $key
     * @return int
     */
    public static function  scard($key){
        $redis = self::init();
        return $redis->scard($key);
    }

    /**
     * 移除并返回集合中的一个随机元素
     * @param $key
     * @return string
     */
    public static function  sPop($key){
        $redis = self::init();
        return $redis->sPop($key);
    }

    /**
     * 返回集合中的一个或多个随机成员元素，返回元素的数量和情况由函数的第二个参数count决定：
     * 如果count为正数，且小于集合基数，那么命令返回一个包含count个元素的数组，数组中的元素各不相同。
     * 如果count大于等于集合基数，那么返回整个集合。
     * 如果count为负数，那么命令返回一个数组，数组中的元素可能会重复出现多次，而数组的长度为count的绝对值。
     * @param $key
     * @param $count
     * @return array|string
     */
    public static function  sRandMember($key, $count){
        $redis = self::init();
        return $redis->sRandMember($key, $count);
    }

    /**
     * 移除集合中指定的一个元素，忽略不存在的元素。删除成功返回1，否则返回0
     * @param $key
     * @param $member
     * @return int
     */
    public static function  srem($key, $member){
        $redis = self::init();
        return $redis->srem($key, $member);
    }

    /**
     * 迭代集合中的元素。
     * 参数：key，迭代器变量，匹配模式，每次返回元素数量（默认为10个）
     * @param $key
     * @param $iterator
     * @param string $pattern
     * @param $count
     * @return array|bool
     */
    public static function  sscan($key, $iterator, $pattern = '', $count=10){
        $redis = self::init();
        return $redis->sscan($key, $iterator, $pattern, $count);
    }

    /**
     * 将指定成员从一个源集合移动到一个目的集合。若源集合不存在或不包含指定元素则不做任何操作，返回false
     * 参数：源集合，目标集合，移动元素
     * @param $srcKey
     * @param $dstKey
     * @param $member
     * @return bool
     */
    public static function  sMove($srcKey, $dstKey, $member){
        $redis = self::init();
        return $redis->sMove($srcKey, $dstKey, $member);
    }

    /**
     * 返回所有给定集合之间的差集，不存在的集合视为空集
     * @param $key1
     * @param $key2
     * @return array
     */
    public static function  sDiff($key1, $key2){
        $redis = self::init();
        return $redis->sDiff($key1, $key2);
    }

    /**
     * 将所有给定集合之间的差集存储在指定的目的集合中。若目的集合已存在则覆盖它。返回差集元素个数
     * 参数：第一个参数为目标集合，存储差集
     * @param $key1
     * @param $key2
     * @return int
     */
    public static function  sDiffStore($dstKey, $key1, $key2){
        $redis = self::init();
        return $redis->sDiffStore($dstKey, $key1, $key2);
    }

    /**
     * 返回所有给定集合的交集，不存在的集合视为空集
     * @param $key1
     * @param $key2
     * @return array
     */
    public static function  sInter($key1, $key2){
        $redis = self::init();
        return $redis->sInter($key1, $key2);
    }

    /**
     * 将所有给定集合的交集存储在指定的目的集合中。若目的集合已存在则覆盖它。返回交集元素个数
     * 参数：第一个参数为目标集合，存储交集
     * @param $dstKey
     * @param $key1
     * @param $key2
     * @return int
     */
    public static function  sInterStore($dstKey, $key1, $key2){
        $redis = self::init();
        return $redis->sInterStore($dstKey, $key1, $key2);
    }

    /**
     * 返回所有给定集合的并集，不存在的集合视为空集
     * @param $key1
     * @param $key2
     * @return array
     */
    public static function  sUnion( $key1, $key2){
        $redis = self::init();
        return $redis->sUnion( $key1, $key2);
    }

    /**
     * 将所有给定集合的并集存储在指定的目的集合中。若目的集合已存在则覆盖它。返回并集元素个数
     * 参数：第一个参数为目标集合，存储并集
     * @param $dstKey
     * @param $key1
     * @param $key2
     * @return int
     */
    public static function  sUnionStore($dstKey, $key1, $key2){
        $redis = self::init();
        return $redis->sUnionStore( $dstKey, $key1, $key2);
    }

    //Set操作结束

}
