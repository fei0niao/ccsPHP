<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Model\SystemRolePermission;
use App\Http\Model\AgentPermission;

class SystemRolePermissionController extends Controller
{
    use \App\Http\Controllers\Load\ShowBaseTrait;

    public static $model_name = 'SystemRolePermission';

    public function __construct()
    {
        parent::__construct();
        $this->middleware([]);
    }

    public function list(Request $request)
    {
        $rs = static::_run_orm($request->all());
        return jsonReturn($rs);
    }

    public function info($id, Request $request)
    {
        $rs = static::_run_orm($request->all(), $id);
        return jsonReturn($rs);
    }

    public function create(Request $request)
    {
        $fills = $request->all();
        $fills['agent_id'] = $this->agent_id;
        $rs = SystemRolePermission::create($fills);
        if (!$rs) return failReturn('创建失败');
        return successReturn('创建成功');
    }

    public function update($role_id, Request $request)
    {
        if (!$role_id) return failReturn('请求失败');
        $agent_id = $this->agent_id;
        try {
            \DB::beginTransaction();
            SystemRolePermission::where(compact('role_id', 'agent_id'))->delete();
            $params = $request->all();
            if (!$params) return successReturn('修改成功');
            if ($agent_id != 1) {
                $agentPermissionIDs = AgentPermission::where('agent_id', $agent_id)->pluck('permission_id')->all();
                if (array_diff($params, $agentPermissionIDs)) {
                    return failReturn('权限范围已变更,请刷新后重新配置!');
                }
            }
            array_walk($params, function (&$v) use ($role_id, $agent_id) {
                $permission_id = $v;
                $v = compact('role_id', 'agent_id', 'permission_id');
            });
            SystemRolePermission::insert($params);
            \DB::commit();
            return successReturn('修改成功');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Log::info('修改权限失败', compact('msg'));
            return failReturn('修改失败');
        }
    }
}