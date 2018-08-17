<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class PlatformFlow extends Base
{
    protected $table = "s_commission_flow";

    protected $guarded = ['id'];

    public static $ConfigAppends= [];

    protected $roundFields = ['accounting_money'];

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        /*$agent_id = Auth::user()->agent_id;
        return $query->where('agent_id',$agent_id);*/
        return $query;
    }

    //账号状态 accounting_type
    public function getNewAccountingTypeAttribute()
    {
        switch ($this->attributes['accounting_type']) {
            case 0:
                return '佣金充值';
            case 1:
                return '佣金扣费';
            default:
                return '未知';
        }
    }
}
