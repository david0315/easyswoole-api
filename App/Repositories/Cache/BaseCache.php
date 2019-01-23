<?php

namespace App\Repositories\Cache;

use App\Utility\Pool\RedisObject;
use App\Utility\Pool\RedisPool;
use EasySwoole\Component\Pool\Exception\PoolEmpty;
use EasySwoole\Component\Pool\Exception\PoolUnRegister;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Logger;

abstract class BaseCache
{
    private $redisLink;
    private $redisPool;
    private $tryTimes = 3;

    function __construct(RedisObject $redis = null)
    {
        if($redis && $redis instanceof RedisObject){
            $this->redisLink = $redis;
        }else{
            Logger::getInstance()->console('===========================-BaseModel---__construct--run-');
            for ($i = 0; $i < $this->tryTimes; $i++) {
                $this->redisPool = PoolManager::getInstance()->getPool(RedisPool::class);
                if($this->redisPool instanceof RedisPool){
                    $timeout = Config::getInstance()->getConf('REDIS.POOL_TIME_OUT');
                    $redisLink = $this->redisPool->getObj($timeout);
                    if($redisLink instanceof RedisObject){
                        $this->redisLink = $redisLink;
                        break;
                    }else{
                        $this->redisLink = null;
                    }
                }else{
                    $this->redisPool = null;
                }
            }
            if(!$this->redisPool){
                throw new PoolUnRegister(RedisPool::class.'pool is unregister');
            }
            if(!$this->redisLink){
                throw new PoolEmpty(RedisPool::class.' pool is empty');
            }
        }
    }

    function __destruct()
    {
        if($this->redisPool instanceof  RedisPool && $this->redisLink instanceof RedisObject){
            Logger::getInstance()->console('--BaseCache--__destruct--run-');
            $this->redisPool->recycleObj($this->redisLink);
        }
        if($this->redisLink instanceof RedisObject){
            PoolManager::getInstance()->getPool(RedisPool::class)->recycleObj($this->redisLink);
        }
        $this->redisLink = null;
        $this->redisPool = null;
    }

    public function getConnection($redis = null): RedisObject{
        if($redis){
            $this->redisLink = $redis;
        }
        return $this->redisLink;
    }

    public function recycle(){
        if($this->redisPool instanceof  RedisPool && $this->redisLink instanceof RedisObject){
            Logger::getInstance()->console('--BaseCache--recycle--run-');
            $this->redisPool->recycleObj($this->redisLink); # 回收一个对象
//            $this->redisPool->unsetObj($this->redisLink); # 彻底释放一个对象
        }

        if($this->redisLink instanceof RedisObject){
            PoolManager::getInstance()->getPool(RedisPool::class)->recycleObj($this->redisLink);
        }

        $this->redisLink = null;
        $this->redisPool = null;
    }

    /**
     * @param $data
     * @return array
     */
    public function hGetAllParseResponse(& $data){
        $result = [];
        if(!$data){
            return $result;
        }
        $total = count($data);
        for ($i = 0; $i < $total; ++$i) {
            $result[$data[$i]] = $data[++$i];
        }
        return $result;
    }

    /**
     * 对 JSON 格式的字符串进行解码
     * @param $jsonString
     * @return mixed|null
     */
    public function decodeJsonString($jsonString)
    {
        $decodeData = json_decode($jsonString, true);
        return json_last_error() === JSON_ERROR_NONE ? $decodeData : null;
    }
}