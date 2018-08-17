<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\Http\Model\LoginLog;
use App\Http\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Http\Model\SystemParam;
use App\Http\Model\SystemRolePermission;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    use \App\Http\Controllers\Load\ShowBaseTrait;

    public static $model_name = 'User';

    public function __construct()
    {
        parent::__construct();
        $this->middleware([]);
    }

    /**
     * 后台管理员
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
        return jsonReturn($rs);
    }

    public function loginInfo(Request $request)
    {
        $user = Auth::user();
        $userInfo = User::with(['loginLog' => function ($query) {
            $query->orderBy('id', 'desc')->limit(1)->offset(1);
        }])->with('role')->find($user->id);
        $systemParam = SystemParam::getParamValue();
        $permission = SystemRolePermission::getAllRolePermissions(1);
        $rs = compact('userInfo', 'systemParam', 'permission');
        return jsonReturn($rs);
    }

    public function create(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            "name" => ["required", "unique:a_agent_emp"],
        ], [
            "name.require" => "用户名不能为空",
            "name.unique" => "用户名已存在",
        ]);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        $fills = $request->all();
        $fills['password'] = bcrypt($fills['password']);
        $fills['agent_id'] = $this->agent_id;
        $rs = (new User)->fill($fills)->save(); //此处由于user的create方法因为未知原因失效 所以改用save方法
        if (!$rs) return failReturn('创建系统用户失败');
        return successReturn('创建系统用户成功');
    }

    public function update($id, Request $request)
    {
        if (!$fills = $request->only('name', 'nick_name', 'phone', 'password', 'role_id', 'email', 'remark', 'is_forbid')) {
            return failReturn('修改项未授权');
        }
        $validator = \Validator::make($request->all(), [
            "name" => ["required", Rule::unique('a_agent_emp')->ignore($id)]
        ], [
            "name.require" => "用户名不能为空",
            "name.unique" => "用户名已存在",
        ]);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        if (!empty($fills['password'])) $fills['password'] = bcrypt($fills['password']);
        $rs = User::find($id)->fill($fills)->save();
        if (!$rs) return failReturn('修改失败');
        return successReturn('修改成功');
    }

    /**
     * 登录
     * @param Request $request
     */
    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            "username" => "required",
            "password" => "required",
            "captchaId" => "required",
            "captchaCode" => "required"
        ], [
            "username.required" => "用户名不能为空",
            "password.required" => "密码不能为空",
            "captchaId.required" => "验证码不能为空",
            "captchaCode.required" => "验证码不能为空",
        ]);
        if ($validator->fails()) {
            return failReturn($validator->errors()->first());
        }
        if (!Controller::verifyCaptchaCode($request->captchaId, $request->captchaCode)) {
            return failReturn("验证码错误");
        }
        $username = $request->get("username");
        $password = $request->get("password");
        $user = User::where('name', $username)->with('role')->first();
        if (!$user) {
            return failReturn("账号 或 密码错误");
        }
        if ($user->is_forbid) {
            return failReturn("账号已被禁用,请联系客服人员!");
        }
        if (!$user->role->is_enable) {
            return failReturn("所属角色已被禁止登录!");
        }
        $ret = apiLogin($username, $password);
        if (!$ret) return failReturn("账号或密码错误!");
        $ip = $last_ip = $request->ip();
        $ip_info = getIpInfo($ip);
        $user->fill(compact('last_ip'))->save();
        $sys_user_id = $user->id;
        $sys_user_name = $user->name;
        $remark = '后台正常登录';
        $agent_id = $user->agent_id;
        /*if (Redis::hGet('plateform_commission_left', $agent_id) && Redis::hGet('plateform_commission_left', $agent_id) < 0) {
            return failReturn('您已欠费!请及时续费!');
        }*/
        if (Redis::get('plateform_commission_left') && Redis::get('plateform_commission_left') < 0) {
            return failReturn('您已欠费!请及时续费!');
        }
        LoginLog::create(compact('ip', 'ip_info', 'sys_user_id', 'sys_user_name', 'remark', 'agent_id'));
        return jsonReturn($ret, 1, "登录成功");
    }

    /**
     * 注销登陆
     */
    public function logout()
    {
        /*if (Auth::check() && !config('app.debug')) {
            Auth::user()->AauthAcessToken()->delete();
        }*/
        return;
    }

    /**
     * 角色扮演
     */
    public function rolePlay($id)
    {
        $user = User::find($id);
        $ret['token'] = $user->createToken('rolePlay')->accessToken;
        $agent_id = $user->agent_id;
        $ret['admin_domain'] = SystemParam::getParamValue('admin_domain',$agent_id);
        /*$param = SystemParam::getParamValue('', $user->agent_id);
        $ret['web_domain'] = $param['web_domain'];
        $ret['mobile_domain'] = $param['mobile_domain'];
        $ret['admin_domain'] = $param['admin_domain'];*/
        //dd($ret);
        return jsonReturn($ret, 1, "登录成功");
    }
}