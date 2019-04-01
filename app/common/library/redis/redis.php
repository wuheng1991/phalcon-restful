<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/4
 * Time: 17:36
 */
use Phalcon\Cache\Backend\Redis;
use Phalcon\Cache\Frontend\Data as FrontData;

class PhalconRedis{
    protected $redis;
    public function __construct()
    {
        $this->redis = $this->initRedis();

    }

    public function initRedis(){
        $frontCache = new FrontData(
            [
                "lifetime" => 172800,
            ]
        );

        $cache = new Redis(
            $frontCache,
            [
                "host"       => "192.168.0.111",
                "port"       => 6380,
                "auth"       => "123456",
                "persistent" => false,
                "index"      => 0,
            ]
        );
        $cache->_connect();
        return $cache;
    }
}