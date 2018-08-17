<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Model\SystemRolePermission;

class RolePermission
{
    /**
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $uri = \Route::current()->uri;
        //特殊接口 可以允许访问 不需要配置
        $allow_url = ['v1/loginInfo'];
        if (in_array($uri, $allow_url)) return $next($request);
        //获取配置的接口
        $permissions = SystemRolePermission::getAllRolePermissions(2);
        //dd($permissions,$uri);
        if(!in_array($uri,$permissions)) abort(403, '你没有权限');
        return $next($request);
    }
}
