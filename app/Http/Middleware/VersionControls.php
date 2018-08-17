<?php

namespace App\Http\Middleware;

use Closure;

class VersionControls
{
    /**
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('X-CustomHeader') && $request->header('X-CustomHeader') < 201802061000) {
            //return jsonReturn([], 0, 'refresh');//刷新前端
            return jsonReturn([], 2,
                "\n" .
                "    1.优化和已知bug修复!"
            );//刷新前端 code=2是刷新的意思
        }
        return $next($request);
    }
}