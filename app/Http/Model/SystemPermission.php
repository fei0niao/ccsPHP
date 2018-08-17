<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class SystemPermission extends Base
{
    protected $table = "s_system_permission";
    protected $guarded = [];
    public $timestamps = false;
    public static $ConfigAppends = [];

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        if ($agent_id == 1) return $query;
        // 机构配置了多少权限 就有多少权限 todo
        $agentPermission = AgentPermission::where('agent_id', $agent_id)->with('systemPermission')->get()->toArray();
        $permission_ids = [];
        foreach($agentPermission as $v){
            $permission_ids[]=$v['permission_id'];
            $permission_ids[]=$v['system_permission']['pid'];
        }
        return $query->whereIn('id', array_unique($permission_ids));
    }

    //角色权限
    public function rolePermission()
    {
        return $this->hasMany(SystemRolePermission::class, 'permission_id', 'id');
    }

    //机构权限
    public function agentPermission()
    {
        return $this->hasMany(AgentPermission::class, 'permission_id', 'id');
    }

    /**
     * hasMany自己
     */
    public function children()
    {
        return $this->hasMany(self::class, 'pid', 'id');
    }

    /**
     *  获取代理商参数值
     */
    public static function getAllPermissions()
    {
        return self::get();
    }

    /**
     *  获取代理商参数值
     */
    public static function getAllChildrenIDs($id, $level = 5)
    {
        $permissions = (self::getAllPermissions())->toArray();
        if (!$permissions) return [];
        $ids = [];
        $pids = [$id];
        while ($level > 0) {
            $new_pids = [];
            foreach ($permissions as $v) {
                if (in_array($v['pid'], $pids)) {
                    $ids[] = $v['id'];
                    $new_pids[] = $v['id'];
                }
            }
            $pids = $new_pids;
            $level--;
        }
        return $ids;
    }
}