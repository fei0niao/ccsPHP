<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Model\Agent;
use App\Http\Model\SystemParam;
use App\Http\Model\AgentPermission;
use App\Http\Model\User;
use App\Http\Model\SystemRole;
use App\Http\Model\SystemRolePermission;

class AgentController extends Controller
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

    public function create(Request $request)
    {
        $fills = $request->all();
        if (!$fills['basic'] || !$fills['param'] || !$fills['permission']) return failReturn('机构信息不全');
        try {
            \DB::beginTransaction();
            $agent_id = (Agent::create($fills['basic']))->id;
            if (!$agent_id) return failReturn('创建机构失败');
            array_walk($fills['param'], function (&$v) use ($agent_id) {
                $v['agent_id'] = $agent_id;
            });
            SystemParam::insert($fills['param']);
            $permissions = $fills['permission'];
            $agentPermission = [];
            foreach($permissions as $v){
                $permission_id = $v;
                $agentPermission[] = compact('permission_id','agent_id');
            }
            AgentPermission::insert($agentPermission);
            $role_id = (SystemRole::create(['role_name' => '管理员', 'agent_id' => $agent_id]))->id;
            $user = [
                'agent_id' => $agent_id,
                'name' => 'admin' . date('Ymd'),
                'nick_name' => '管理员',
                'phone' => $fills['basic']['phone'],
                'password' => bcrypt('123456'),
                'role_id' => $role_id
            ];
            (new User)->fill($user)->save(); //此处由于user的create方法因为未知原因失效 所以改用save方法
            $rolePermisssion = [];
            foreach($permissions as $v){
                $permission_id = $v;
                $rolePermisssion[] = compact('permission_id','role_id','agent_id');
            }
            SystemRolePermission::insert($rolePermisssion);
            \DB::commit();
            return successReturn('创建成功');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Log::info('创建机构失败', compact('msg'));
            return failReturn('创建失败');
        }
    }

    public function update($id, Request $request)
    {
        if (!$fills = $request->only(['agent_name', 'owner_name', 'phone', 'remark', 'sms_account', 'sms_pwd', 'sms_sign_id'])) {
            return failReturn('修改项未授权');
        }
        $rs = Agent::find($id)->fill($fills)->save();
        if (!$rs) return failReturn('修改失败');
        return successReturn('修改成功');
    }
}