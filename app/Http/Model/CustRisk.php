<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class CustRisk extends Base
{
    protected $table = "u_cust_risk_control";

    protected $guarded = ['id', 'created_time', 'updated_time'];

    public static $ConfigAppends= [];

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        return $query->where('agent_id',$agent_id);
    }

    public function stock()
    {
        return $this->belongsTo(StockInfo::class, 'stock_code', 'stock_code');
    }

    public function getNewStockCodeNameAttribute()
    {
        return $this->attributes['stock_code'] . ' ' . $this->attributes['stock_name'];
    }
}