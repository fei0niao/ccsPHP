<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class SystemRolePermission extends Base
{
    protected $table = "s_system_role_permission";
    protected $guarded = [];
    public $timestamps = false;
    public static $ConfigAppends = [];

    //权限
    public static function permission($query = '')
    {
        $query = $query ?: self::query();
        $agent_id = Auth::user()->agent_id;
        return $query->where('agent_id', $agent_id);
    }

    /**
     *  获取角色权限
     */
    public static function getAllRolePermissions($type = '', $agent_id = '', $role_id = '')
    {
        $agent_id = $agent_id ?: getAgentID();
        $role_id = $role_id ?: Auth::user()->role_id;
        if (!$agent_id || !$role_id) return false;
        return \Cache::tags(__METHOD__)->remember(implode('-', func_get_args()), null, function () use ($agent_id, $role_id, $type) {
            $permission_ids = self::where(compact('agent_id', 'role_id'))->pluck('permission_id')->all();
            switch ($type) {
                case 1:
                    return SystemPermission::whereIn('id', $permission_ids)->get()->pluck('name')->all();
                case 2:
                    $api_ids = SystemPermission::whereIn('id', $permission_ids)->get()->pluck('api_id')->all();
                    //生成去掉逗号和重复元素的数组
                    $api_ids = array_unique(explode(',',implode(',',$api_ids)));
                    //dd($api_ids);
                    return SystemApi::whereIn('id', $api_ids)->get()->pluck('api_route')->all();
                default:
                    return $permission_ids;
            }
        });
    }
}