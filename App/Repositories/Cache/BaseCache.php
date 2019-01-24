<?php

namespace App\Repositories\Cache;

abstract class BaseCache
{
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
