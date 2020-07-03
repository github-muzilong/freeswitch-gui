<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Merchant extends Model
{
    use SoftDeletes;
    protected $table = 'merchant';
    protected $fillable = [
        'company_name',
        'contact_name',
        'contact_phone',
        'staff_num',
        'sip_num',
        'gateway_num',
        'agent_num',
        'queue_num',
        'task_num',
        'expire_at',
        'freeswitch_id',
    ];

}
