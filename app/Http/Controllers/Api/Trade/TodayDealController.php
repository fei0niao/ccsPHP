<?php

namespace App\Http\Controllers\Api\Trade;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Model\TodayDeal;

class TodayDealController extends Controller
{
    use \App\Http\Controllers\Load\ShowBaseTrait;

    public static $model_name = 'TodayDeal';

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
        $rs = TodayDeal::create($fills);
        if (!$rs) return failReturn('创建失败');
        return successReturn('创建成功');
    }

    public function update($id, Request $request)
    {
        if (!$fills = $request->only('capital_name')) {
            return failReturn('修改项未授权');
        }
        $rs = TodayDeal::find($id)->fill($fills)->save();
        if (!$rs) return failReturn('修改失败');
        return successReturn('修改成功');
    }
}
