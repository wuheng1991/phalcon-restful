<?php
// +----------------------------------------------------------------------
// | incrCommand.php [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
namespace App\Utils\Redis\Commands;

class IncrByCommand implements CommandInterface
{
    public static function getScript()
    {
        $script = <<<LUA
    local result = false;
    result = redis.pcall('incrby',KEYS[1],KEYS[2]);
    if(result)
    then
        redis.pcall('expire',KEYS[1],KEYS[3])
    end
    return result;
LUA;
        return $script;
    }
}
