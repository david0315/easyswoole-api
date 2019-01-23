<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 18-11-1
 * Time: 下午1:49
 */

namespace App\Models;


use App\Utility\Pool\MySqlObject;
use App\Utility\Pool\MySqlPool;
use EasySwoole\Component\Pool\AbstractPool;
use EasySwoole\Component\Pool\Exception\PoolEmpty;
use EasySwoole\Component\Pool\Exception\PoolException;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Logger;

class BaseModel
{
    #
    protected $tableName = '';
    # 表字段
    protected $attributeLabels = [];



}
