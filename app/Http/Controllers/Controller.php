<?php

namespace App\Http\Controllers;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OSS\Core\OssException;
use OSS\OssClient;
use Illuminate\Support\Facades\Auth;
use Closure;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const PAGE_SIZE = 15;
    const CAPTCHA_PREFIX = "captcha_";
    const CAPTCHA_CACHE = "redis";
    public $user = '';
    public $agent_id = '';

    public function __construct()
    {
        $request = request();
        //自Laravel 5.3 开始把路由分组以后，就有这个问题了。原因是 construct 运行时 middleware 未运行
        $this->middleware(function($request,Closure $next){
            if($request->header('Authorization')){
                $this->user = Auth::user();
                if($this->user) $this->agent_id = $this->user->agent_id;
            }
            return $next($request);
        },['except' => ['login','logout']]);
    }

    /**
     * 获取验证码 重新获取验证码
     * @param $captchaId ,$captchaCode
     * @return bool
     */
    static function verifyCaptchaCode($captchaId, $captchaCode): bool
    {
        $cacheKey = self::CAPTCHA_PREFIX . $captchaId;
        $cachedCode = Cache::store(self::CAPTCHA_CACHE)->get($cacheKey);
        //Cache::forget($cacheKey);
        return $cachedCode == $captchaCode;
    }

    /**
     * 设置图片验证码
     * @param $captchaId
     * @return string 返回图片base64 string
     */
    static function generateCaptchaImage($captchaId): string
    {
        $phraseBuilder = new PhraseBuilder(5, '0123456789');
        $builder = new CaptchaBuilder(null, $phraseBuilder);
        $builder->setDistortion(false);
        $builder->setIgnoreAllEffects(true);
        $builder->build();
        $cacheKey = self::CAPTCHA_PREFIX . $captchaId;
        Cache::store(self::CAPTCHA_CACHE)->put($cacheKey, $builder->getPhrase(), 5);
        return $builder->inline();
    }

    //暂时没用 缓存时会很有用
    static function generateCacheKeyByReqeust()
    {
        $request = request();
        $uri = $request->getUri();
        return $uri . '.' . http_build_query($request->all());
    }
}