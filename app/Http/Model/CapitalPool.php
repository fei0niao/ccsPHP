<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class CapitalPool extends Base
{
    protected $table = "s_capital_pool";

    protected $guarded = ['id', 'created_time', 'updated_time'];

    public static $ConfigAppends= [];

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        return $query->where('agent_id',$agent_id);
    }
}
