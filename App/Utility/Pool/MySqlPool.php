<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/26
 * Time: 4:43 PM
 */

namespace App\Utility\Pool;

use EasySwoole\Component\Pool\AbstractPool;
use EasySwoole\EasySwoole\Config;

class MySqlPool extends AbstractPool
{

    protected function createObject()
    {
        // TODO: Implement createObject() method.
        /**
         * 创建对象的时候，请加try，尽量不要抛出异常
         */
        $return = null;
        try {
            $dbConf = new \EasySwoole\Mysqli\Config(Config::getInstance()->getConf('MYSQL'));
            $return = new MySqlObject($dbConf);
            unset($dbConf);
        } catch (\Throwable $throwable) {
            // to do something...
        } finally {
            return $return;
        }
    }

}