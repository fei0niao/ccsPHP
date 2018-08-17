<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class AgentPermission extends Base
{
    protected $table = "s_system_agent_permission";
    protected $guarded = [];
    public $timestamps = false;
    public static $ConfigAppends = [];

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        return $query->where('agent_id', $agent_id);
    }

    //机构权限
    public function systemPermission()
    {
        return $this->belongsTo(systemPermission::class, 'permission_id', 'id');
    }

    //获取机构权限范围
    public function getAllAgentPermission()
    {
        $agent_id = Auth::user()->agent_id;
        return self::where(compact('agent_id'))->get()->pluck('permission_id')->all();
    }
}