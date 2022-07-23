<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Gateway extends Model
{
    protected $table = 'gateway';
    protected $guarded = ['id'];

    /**
     * 查询网关状态
     * @param $id
     * @return string
     */
    public function getStatus($id)
    {
        $fs = new \Freeswitchesl();
        $service = config('freeswitch.esl');
        try{
            $fs->connect($service['host'], $service['port'], $service['password']);
            $result = $fs->api("sofia status gateway gw".$id);
            $data = trim($result);
            if ($data=="Invalid Gateway!"){
                return $data;
            }
            foreach (explode("\n",$data) as $item){
                $itemArr = explode("\t",$item);
                if (trim($itemArr[0])=="State"){
                    return $itemArr[1];
                }
            }
            $fs->disconnect();
        }catch (\Exception $exception){
            Log::info('查询网关状态ESL连接异常：'.$exception->getMessage());
            return '连接失败';
        }
    }


}
