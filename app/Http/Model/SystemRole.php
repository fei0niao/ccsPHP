<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class SystemRole extends Base
{
    protected $table = "s_system_role";
    protected $guarded = ['id'];
    public $timestamps = false;
    public static $ConfigAppends= [];

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        return $query->where('agent_id',$agent_id);
    }
}
