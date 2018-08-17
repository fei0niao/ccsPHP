<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Model\Cust;
use Illuminate\Validation\Rule;

/**
 * Class ClientController 客户表
 * @package App\Http\Controllers\Api
 */
class ClientController extends Controller
{
    use \App\Http\Controllers\Load\ShowBaseTrait;

    public static $model_name = 'Cust';

    public function __construct()
    {
        parent::__construct();
        $this->middleware([]);
    }

    /**
     * 客户列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $rs = static::_run_orm($request->all());
        return jsonReturn($rs);
    }

    public function info($id, Request $request)
    {
        $rs = static::_run_orm($request->all(), $id);
        return jsonReturn($rs->getAttributes());
    }

    public function create(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            "cellphone" => ["required", "regex:/^1[0-9]{10}$/",
                Rule::unique('u_customer')->where(function ($query) {
                    $query->where('agent_id', $this->agent_id);
                })
            ],
        ], [
            "cellphone.require" => "手机号不能为空",
            "cellphone.regex" => "手机号格式错误",
            "cellphone.unique" => "手机号已注册",
        ]);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        $fills = $request->all();
        $fills['password'] = md5($fills['password']);
        $fills['agent_id'] = $this->agent_id;
        $rs = Cust::create($fills);
        if (!$rs) return failReturn('创建客户失败');
        return successReturn('创建客户成功');
    }

    public function update($id, Request $request)
    {
        if (!$fills = $request->only('real_name', 'cellphone', 'remark', 'password', 'is_login_forbidden')) {
            return failReturn('修改项未授权');
        }
        $validator = \Validator::make($request->all(), [
            "cellphone" => ["regex:/^1[0-9]{10}$/", Rule::unique('u_customer')->ignore($id)]
        ], [
            "cellphone.regex" => "手机号格式错误",
            "cellphone.unique" => "手机号已注册",
        ]);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        if (!empty($fills['password'])) $fills['password'] = md5($fills['password']);
        $rs = Cust::find($id)->fill($fills)->save();
        if (!$rs) return failReturn('修改失败');
        return successReturn('修改成功');
    }
}