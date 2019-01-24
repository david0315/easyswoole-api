<?php

namespace App\Repositories;

use App\Models\SyncVersion;
use App\Models\SyncVersionDetail;
use App\Utility\Pool\MySqlObject;
use EasySwoole\Component\Singleton;

class SyncVersionRepository extends BaseRepository{

    use Singleton;

    protected  $versionModel = null;
    protected  $versionDetailModel = null;

    public function __construct()
    {
        parent::__construct();
        $this->versionModel = new SyncVersion();
        $this->versionDetailModel = new SyncVersionDetail();
    }

    /**
     * @param $json
     * @param $type
     * @param $createAt
     * @return bool
     * @throws \EasySwoole\Mysqli\Exceptions\ConnectFail
     * @throws \Throwable
     */
    public function createVersion($json, $type, $createAt){

        $syncData = [
            'type' => $type,
            'json' => $json,
            'created_at' => $createAt
        ];
        try{

            $this->getDBConnection()->startTransaction();

            $this->getDBConnection()->insert($this->versionModel->tableName, $syncData);

            $this->_throwDBException('存储 Version 错误:参数错误', $this->getDBConnection()->getLastErrno());

            $syncId = $this->getDBConnection()->getInsertId();

            $syncDetailData = $this->_makeDetail($targets, $syncId, $type, $createAt);

            $this->getDBConnection()->insertMulti($this->versionDetailModel->tableName, $syncDetailData);

            $this->_throwDBException('存储 Version Detail 错误:参数错误', $this->getDBConnection()->getLastErrno());

            $this->getDBConnection()->commit();

            return true;
        }catch (\Exception $e){
            !$this->getDBConnection() ?: $this->getDBConnection()->rollback();
            $this->console($e->getTraceAsString());

            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }




    private function _throwDBException($message, $code){
        if($this->getDBConnection()->getLastErrno()){
            $this->console("SQL::err_no::{$this->getDBConnection()->getLastErrno()}::err_msg::{$this->getDBConnection()->getLastError()}::query_Sql::{$this->getDBConnection()->getLastQuery()}");
            throw new \Exception($message, $code);
        }
    }

    private function _makeDetail(&$targets, $versionId, $type, $createAt){
        $syncDetailData = [];
        foreach ($targets as $key=>$item){
            $syncDetailData[] = [
                'version_id' => $versionId,
                'type' => $type,
                'target' => $item,
                'status' => 1,
                'created_at' => $createAt,
                'updated_at' => $createAt
            ];
        }
        return $syncDetailData;
    }


    public function updateVersionDetail($detailId){

        try{

            $detailInfo = $this->getDBConnection()->where('id', $detailId)->getOne($this->versionDetailModel->tableName, $this->versionDetailModel->attributeLabels);

            return $detailInfo;

        }catch (\Exception $e){

        }finally{

        }
    }

    public function findSyncVersion($versionId){
        try{
            $info = $this->getDBConnection()->where('id', $versionId)->getOne($this->versionModel->tableName, $this->versionModel->attributeLabels);
            return $info;
        }catch (\Exception $e){
            $this->console($e->getTraceAsString());
        }
    }

    /**
     * @param array $attributes
     * @return mixed|void
     */
    protected function serialization(array $attributes)
    {
        // TODO: Implement serialization() method.
    }

}