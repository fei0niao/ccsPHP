<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Model\AgentPermission;
use App\Http\Model\SystemRolePermission;

class AgentPermissionController extends Controller
{
    use \App\Http\Controllers\Load\ShowBaseTrait;

    public static $model_name = 'Agent';

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

    public function update($agent_id, Request $request)
    {
        if (!$agent_id) return failReturn('请求失败');
        try {
            \DB::beginTransaction();
            AgentPermission::where(compact('agent_id'))->delete();
            $params = $permission_ids =  $request->all();
            array_walk($params, function (&$v) use ($agent_id) {
                $permission_id = $v;
                $v = compact('agent_id', 'permission_id');
            });
            AgentPermission::insert($params);
            SystemRolePermission::where(compact('agent_id'))->whereNotIn('permission_id',$permission_ids)->delete();
            \DB::commit();
            return successReturn('修改成功');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Log::info('修改失败', compact('msg'));
            return failReturn('修改失败');
        }
    }
}