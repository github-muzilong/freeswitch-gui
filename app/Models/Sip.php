<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sip extends Model
{
    protected $table = 'sip';
    protected $fillable = [
        'username',
        'password',
        'effective_caller_id_name',
        'effective_caller_id_number',
        'outbound_caller_id_name',
        'outbound_caller_id_number',
        'state',
        'status',
        'freeswitch_id',
        'merchant_id',
        'gateway_id',
        'staff_id',
    ];

    /**
     * 所属FS
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function freeswitch()
    {
        return $this->hasOne(Freeswitch::class,'id','freeswitch_id')->withDefault([
            'name' => '-',
        ]);
    }

    /**
     * 所属商户
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function merchant()
    {
        return $this->hasOne(Merchant::class,'id','merchant_id')->withDefault([
            'company_name' => '-',
        ]);
    }

    /**
     * 绑定网关
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function gateway()
    {
        return $this->hasOne(Gateway::class,'id','gateway_id')->withDefault([
            'name' => '-',
        ]);
    }

}
