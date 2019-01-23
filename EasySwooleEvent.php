<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;

use App\Process\HotReload;
use App\Utility\Pool\MySqlPool;
use App\Utility\Pool\RedisPool;

use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');

        PoolManager::getInstance()->register(MySqlPool::class, Config::getInstance()->getConf('MYSQL.POOL_MAX_NUM'))->setMinObjectNum(Config::getInstance()->getConf('MYSQL.POOL_MIN_NUM'));

        PoolManager::getInstance()->register(RedisPool::class, Config::getInstance()->getConf('REDIS.POOL_MAX_NUM'))->setMinObjectNum(Config::getInstance()->getConf('REDIS.POOL_MIN_NUM'));

    }

    public static function mainServerCreate(EventRegister $register)
    {
        // TODO: Implement mainServerCreate() method.
        $swooleServer = ServerManager::getInstance()->getSwooleServer();
        $swooleServer->addProcess((new HotReload('HotReload', ['disableInotify' => false]))->getProcess());

        /**
         * 新增preload方法,可在程序启动后预创建连接,避免在启动时突然大量请求,造成连接来不及创建从而失败的问题. 示例: 在EasySwooleEvent文件,mainServerCreate事件中增加onWorkerStart回调事件中预热启动:
         */
        $register->add($register::onWorkerStart, function (\swoole_server $server, int $workerId){
            if ($server->taskworker == false) {
                PoolManager::getInstance()->getPool(MySqlPool::class)->preLoad(Config::getInstance()->getConf('MYSQL.POOL_MIN_NUM'));
                PoolManager::getInstance()->getPool(RedisPool::class)->preLoad(Config::getInstance()->getConf('REDIS.POOL_MIN_NUM'));
                //PoolManager::getInstance()->getPool(RedisPool::class)->preLoad(预创建数量,必须小于连接池最大数量);
            }
        });

    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        $response->withHeader('Access-Control-Allow-Origin', '*');
        $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        if ($request->getMethod() === 'OPTIONS') {
            $response->withStatus(Status::CODE_OK);
            $response->end();
        }
        # 设置远程IP
        if (!$response->isEndResponse()) {
            $request->withAttribute('currentTimestamp', time());
            $request->withAttribute('request_time', microtime(true));
            $ip = ServerManager::getInstance()->getSwooleServer()->connection_info($request->getSwooleRequest()->fd);
            $request->withAttribute('remote_ip', isset($ip['remote_ip']) ? $ip['remote_ip'] : 'Unknown');
        }
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }

    public static function onReceive(\swoole_server $server, int $fd, int $reactor_id, string $data):void
    {

    }


}
