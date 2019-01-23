<?php
/**
 * Created by PhpStorm.
 * User: david.li
 * Date: 14/1/19
 * Time: 15:07
 */

namespace App\Models;

/**
 * Class SyncVersion
 * @package App\Model
 * Date: 14/1/19
 * Time: 15:11
 * Author: david.li
 */
class SyncVersion extends BaseModel
{
    public $tableName = 'xh_sync_versions';

    public $attributeLabels = ['id','type','json','created_at'];
}