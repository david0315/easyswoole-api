<?php
/**
 * Created by PhpStorm.
 * User: david.li
 * Date: 24/12/18
 * Time: 15:10
 */

namespace App\HttpController;

use App\Repositories\SyncVersionRepository;

/**
 * 测试用例
 * Class Meta
 * @package App\HttpController\Api\V1
 * Date: 24/12/18
 * Time: 15:10
 * Author: david.li
 */

class Test extends BaseController
{
    public function index()
    {
        try{
//            $a1 = Sync1Repository::getInstance()->findSyncVersion(7);
//            print_r($a1);
//            $a2 = Sync2Repository::getInstance()->findSyncVersion(8);
//            print_r($a2);
//            $a3 = Sync3Repository::getInstance()->findSyncVersion(9);
//            print_r($a3);
//            $a4 = Sync4Repository::getInstance()->findSyncVersion(10);
//            print_r($a4);
//            $a5 = Sync5Repository::getInstance()->findSyncVersion(11);
//            print_r($a5);
//            $a6 = Sync6Repository::getInstance()->findSyncVersion(12);
//            print_r($a6);
//            $a7 = Sync7Repository::getInstance()->findSyncVersion(13);
//            print_r($a7);

            $a = (new SyncVersionRepository());
//            print_r($a);
//            $a = SyncVersionRepository::getInstance()->findSync(4);

            return $this->writeJson(200, 'ok', $a);
        }catch (\Exception $e){
//            echo $e->getCode();
            return $this->setErrorCodeMessage($e->getCode(), $e->getMessage());
//            return $this->setErrorCode($e->getCode());
        }

//        $result = AuthTokenCache::getInstance()->get();
//        var_dump($result);
//        Logger::getInstance()->console();
        // TODO: Implement index() method.
    }

    public function create(){

        var_dump($this->request()->getParsedBody());  # getRequestParam()
//        $this->request()->getParsedBody(); # 获得post内容
//        $this->request()->getQueryParam(); # 获得get内容

    }

}