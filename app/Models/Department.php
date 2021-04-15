<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;
    protected $table = 'department';
    protected $fillable = [
        'merchant_id',
        'parent_id',
        'name',
        'sort',
        'created_staff_id',
    ];

    //子部门
    public function child()
    {
        return $this->hasMany('App\Models\Department','parent_id','id');
    }

    //所有子递归
    public function childs()
    {
        return $this->child()->with('childs');
    }

    /**
     * 所属商户
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function merchant()
    {
        return $this->hasOne(Merchant::class,'id','merchant_id')->withDefault([
            'company_name' => '-'
        ]);
    }

}
