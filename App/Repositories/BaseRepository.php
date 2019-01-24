<?php

namespace App\Repositories;

use App\Utility\Pool\MySqlObject;
use App\Utility\Pool\MySqlPool;
use App\Utility\Pool\RedisObject;
use App\Utility\Pool\RedisPool;
use EasySwoole\Component\Pool\Exception\PoolEmpty;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Logger;

/**
 * 抽象的 Repository 类
 *
 * @package App\Repositories
 */
abstract class BaseRepository{

    public static $close_cache = false; // true 关闭缓存 false 开启缓存
    const PER_PAGE = 15;

    private $dbLink;
    private $dbPool;

    private $redisLink;
    private $redisPool;
    
    private $tryTimes = 3;
    protected $currentTimestamp;

    protected function getDBConnection():MySqlObject{
        return $this->dbLink;
    }

    public function __construct()
    {
//        $this->makeAsyncResource();
        $this->makeResource();
        $this->console('init BaseRepository __construct');
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->console('init BaseRepository __destruct');
        $this->recycleResource();
    }

    private function makeResource(){
        for ($i = 0; $i < $this->tryTimes; $i++) {
            Logger::getInstance()->console('====================makeResource=======-BaseRepository---__construct--run-'.$i);
            $this->dbPool = PoolManager::getInstance()->getPool(MySqlPool::class);
            if($this->dbPool instanceof MySqlPool){
                $timeout = Config::getInstance()->getConf('MYSQL.POOL_TIME_OUT');
                $dbLink = $this->dbPool->getObj($timeout);
                if($dbLink instanceof MySqlObject){
                    $this->dbLink = $dbLink;
                    break;
                }else{
                    $this->dbLink = null;
                }
            }
        }
        if(!$this->dbLink){
            throw new PoolEmpty(MySqlPool::class.' pool is empty');
        }

        for ($i = 0; $i < $this->tryTimes; $i++) {
            Logger::getInstance()->console('=====================makeResource======-BaseRepository---__construct--run-');
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
                $redisPool = null;
            }
        }

        if(!$this->redisLink){
            throw new PoolEmpty(RedisPool::class.' pool is empty');
        }
    }

    private function makeAsyncResource(){
        go(function (){
            for ($i = 0; $i < $this->tryTimes; $i++) {
                Logger::getInstance()->console('===================makeAsyncResource========-BaseRepository---__construct--run-'.$i);
                $this->dbPool = PoolManager::getInstance()->getPool(MySqlPool::class);
                if($this->dbPool instanceof MySqlPool){
                    $timeout = Config::getInstance()->getConf('MYSQL.POOL_TIME_OUT');
                    $dbLink = $this->dbPool->getObj($timeout);
                    if($dbLink instanceof MySqlObject){
                        $this->dbLink = $dbLink;
                        Logger::getInstance()->console('===================makeAsyncResource========-Success-db--__construct--run-'.$i);
                        unset($dbPool);
                        break;
                    }else{
                        $this->dbLink = null;
                    }
                }
            }
            if(!$this->dbLink){
                throw new PoolEmpty(MySqlPool::class.' pool is empty');
            }
        });

        go(function(){
            for ($i = 0; $i < $this->tryTimes; $i++) {
                Logger::getInstance()->console('====================makeAsyncResource=======-BaseRepository---__construct--run-');
                $this->redisPool = PoolManager::getInstance()->getPool(RedisPool::class);
                if($this->redisPool instanceof RedisPool){
                    $timeout = Config::getInstance()->getConf('REDIS.POOL_TIME_OUT');
                    $redisLink = $this->redisPool->getObj($timeout);
                    if($redisLink instanceof RedisObject){
                        $this->redisLink = $redisLink;
                        Logger::getInstance()->console('===================makeAsyncResource========-Success-redis--__construct--run-'.$i);
                        break;
                    }else{
                        $this->redisLink = null;
                    }
                }else{
                    $redisPool = null;
                }
            }

            if(!$this->redisLink){
                throw new PoolEmpty(RedisPool::class.' pool is empty');
            }
        });
    }

    private function recycleResource(){
        Logger::getInstance()->console('===========================-BaseRepository---recycleResource-run-');
        $this->dbPool->recycleObj($this->dbLink);
        $this->redisPool->recycleObj($this->redisLink);
        unset($this->dbPool, $this->redisPool, $this->dbLink, $this->redisLink);
    }

    public function console($message){
        Logger::getInstance()->console($message);
    }

    /**
     * 序列化模型实例
     *
     * @param array $attributes
     * @return mixed
     */
    abstract protected function serialization(array $attributes);


    /**
     * 比较差异
     * @param $requestData
     * @param $dbData
     * @return array
     */
    public function diffData(&$requestData, &$dbData)
    {
        $diffData = [];
        foreach ($requestData as $key => $item) {
            if (!isset($dbData[$key])) {
                $diffData[$key] = $item;
            }
            if (isset($dbData[$key]) && $dbData[$key] != $item) {
                $diffData[$key] = $item;
            }
        }
        return $diffData;
    }


}
