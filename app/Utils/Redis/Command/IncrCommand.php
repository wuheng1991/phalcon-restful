<?php
// +----------------------------------------------------------------------
// | incrCommand.php [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
namespace App\Utils\Redis\Commands;

class IncrCommand implements CommandInterface
{
    public static function getScript()
    {
        $script = <<<LUA
    local result = 0;
    result = redis.pcall('incr',KEYS[1]);
    if(result)
    then
        redis.pcall('expire',KEYS[1],KEYS[2])
    end
    return result;
LUA;
        return $script;
    }
}
