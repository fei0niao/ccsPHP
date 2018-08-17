<?php

namespace App\Http\Controllers\Api\Contract;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Model\ParentAccount;
use App\Http\Controllers\Api\RequestApiController;

class ParentAccountController extends Controller
{
    use \App\Http\Controllers\Load\ShowBaseTrait;

    public static $model_name = 'ParentAccount';

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
        $rs = ParentAccount::create($fills);
        if (!$rs) return failReturn('创建母账户失败');
        return successReturn('创建母账户成功');
    }

    public function update($id, Request $request)
    {
        if (!$fills = $request->only('financier_name','account','total_in_capital','securities_trader','capital_id','account_status','available_capital','password','communication_pw')) {
            return failReturn('修改项未授权');
        }
        $parentAccount = ParentAccount::find($id);
        if(!empty($fills['account']) && !empty($fills['password']) && !empty($fills['communication_pw']) && ($parentAccount->account != $fills['account'] || $parentAccount->account != $fills['password'] || $parentAccount->communication_pw != $fills['communication_pw'])){
            $fills['password'] = opensslEncode($fills['password']);
            $fills['communication_pw'] = opensslEncode($fills['communication_pw']);
            $rs_request = (new RequestApiController)->updateParentStockActPwd($id,$fills);
            if($rs_request->getStatusCode() != 1); return failReturn('母账户账号密码修改失败');
        }
        $rs = $parentAccount->fill($fills)->save();
        if (!$rs) return failReturn('修改失败');
        return successReturn('修改成功');
    }
}
