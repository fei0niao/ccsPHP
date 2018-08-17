<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class HistoryParentDeal extends Base
{
    protected $table = "u_parent_stock_finance_day_makedeal_history";

    protected $guarded = ['id', 'created_time', 'updated_time'];

    public static $ConfigAppends= [];

    public function __construct()
    {
        parent::__construct();
        //如果不是管理员
        if (Auth::user()->agent_id != 1) {
        }
    }

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        return $query->where('agent_id',$agent_id);
    }

    /**
     * 成交日期时间
     */
    public function getNewMakedealDateTimeAttribute()
    {
        return $this->attributes['makedeal_date'] . " " . $this->attributes['makedeal_time'];
    }
}
