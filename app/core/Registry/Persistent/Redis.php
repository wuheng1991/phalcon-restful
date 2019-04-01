<?php
// +----------------------------------------------------------------------
// | Redis.php
// +----------------------------------------------------------------------
namespace App\Core\Registry\Persistent;

use Xin\Redis as Client;

class Redis
{
    public static function getInstance()
    {
        $config = di('config');

        $host = $config->redis->host;
        $port = $config->redis->port;
        $auth = $config->redis->auth;
        $db = $config->redis->index;

        return Client::getInstance($host, $auth, $db, $port, 'registry');
    }

}