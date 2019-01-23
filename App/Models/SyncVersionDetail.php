<?php
/**
 * Created by PhpStorm.
 * User: david.li
 * Date: 14/1/19
 * Time: 15:08
 */

namespace App\Models;

/**
 * Class SyncVersionDetail
 * @package App\Model
 * Date: 14/1/19
 * Time: 15:11
 * Author: david.li
 */
class SyncVersionDetail extends BaseModel
{
    public $tableName = 'xh_sync_version_details';

    public $attributeLabels = ['id','version_id','type', 'target', 'status', 'created_at', 'updated_at'];
}