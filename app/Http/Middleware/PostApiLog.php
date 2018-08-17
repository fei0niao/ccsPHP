<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use Lcobucci\JWT\Parser;

/**
 * 记录管理后台的全部操作日志,保存数据到Redis里面去
 * Class PostApiLog
 * @package App\Http\Middleware
 */
class PostApiLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //api 访问统计前置
        $uri = $request->getUri();
        Redis::HINCRBY('admin_api_count', $uri, 1);

        //统计IP地址访问 计数
        $ip = $request->ip();
        Redis::HINCRBY('admin_api_ip_visit_count', $ip, 1);

        //统计浏览器UA 计数
        $userAgent = (string)$request->header("User-Agent");
        Redis::HINCRBY('admin_api_user_agent_count', $userAgent, 1);


        //而下面（这种写法的）中间件会在应用处理请求 之后 执行其任务：
        $response = $next($request);
        //是post方法就记录相关日志
        //只记录请求成功的200记录
        if (in_array($request->method(), ['POST', 'GET']) && $response->status() == 200) {
            $referer = (string)$request->header("Referer");
            $time = date("Y-m-d H:i:s");
            $parameters = $request->all();
            //记录用户id
            $employee_id = 0;
            $authorization = $request->header("Authorization");
            if ($authorization) {
                $jwt = trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $authorization));
                try {
                    $token = (new Parser())->parse($jwt);
                    $employee_id = $token->getClaim("sub");
                } catch (\Exception $e) {
                }
            }
            $value = json_encode(compact('uri', 'ip', 'referer', 'time', 'employee_id', 'parameters'));
            Redis::LPUSH('admin_api_logs', $value);
            Redis::lTrim('admin_api_logs', 0, 100000);
        }
        return $response;
    }
}
