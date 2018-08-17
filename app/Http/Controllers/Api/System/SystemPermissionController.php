<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\Http\Model\SystemRolePermission;
use Illuminate\Http\Request;
use App\Http\Model\SystemPermission;

class SystemPermissionController extends Controller
{
    use \App\Http\Controllers\Load\ShowBaseTrait;

    public static $model_name = 'SystemPermission';

    public function __construct()
    {
        parent::__construct();
        $this->middleware([]);
    }

    public function list($istree = 0, Request $request)
    {
        $rs = static::_run_orm($request->all());
        /*if ($istree && $rs['list']) {
            $rs['list'] = getTree($rs['list']->toArray());
        }*/
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
        $rs = SystemPermission::create($fills);
        if (!$rs) return failReturn('创建失败');
        return successReturn('创建成功');
    }

    public function update($id, Request $request)
    {
        if (!$fills = $request->only(['name', 'api_id', 'title', 'remark'])) {
            return failReturn('修改项未授权');
        }
        $rs = SystemPermission::find($id)->fill($fills)->save();
        if (!$rs) return failReturn('修改失败');
        return successReturn('修改成功');
    }

    public function destroy($id, Request $request)
    {
        $ids = SystemPermission::getAllChildrenIDs($id);
        array_push($ids, (int)$id);
        try {
            \DB::beginTransaction();
            SystemPermission::destroy($ids);
            SystemRolePermission::whereIn('permission_id', $ids)->delete();
            \DB::commit();
            return successReturn('删除成功');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Log::info('删除权限失败', compact('msg'));
            return failReturn('删除失败');
        }
    }
}