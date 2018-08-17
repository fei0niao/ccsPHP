<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class SystemParam extends Base
{
    protected $table = "s_system_params";

    protected $guarded = [];

    public static $ConfigAppends = [];

    //权限
    public static function permission($query = '')
    {
        $query = $query ?: self::query();
        /*$c_query = clone $query;
        $agent_id = Auth::user()->agent_id;
        $id = $c_query->selectRaw('max(id) as id')->whereIn('agent_id', [0, $agent_id])->groupBy(['param_key'])->orderBy('id')->pluck('id')->toArray();
        return $query->whereIn('id', $id)->where('is_show', '>', 0);*/
        $agent_id = Auth::user()->agent_id;
        if ($agent_id == 1) return $query;
        return $query->whereIn('agent_id', [-1, $agent_id])->where('is_show', '>', 0);
    }

    /**
     *  格式化
     */
    public function getNewParamValueAttribute()
    {
        switch ($this->attributes['param_type']) {
            case 1:
                return $this->attributes['param_value'];
            case 2:
                return $this->attributes['param_value'] ? '是' : '否';
            case 3:
                return 100 * $this->attributes['param_value'] . '%';
            default:
                return $this->attributes['param_value'];
        }
    }

    /**
     *  获取代理商参数值
     */
    public static function getParamValue($param_key = '', $agent_id = '')
    {
        $agent_id = $agent_id ?: getAgentID();
        /*$rs = \Cache::remember('getParamValue' . $agent_id . $param_key, null, function () use ($agent_id) {
            $list = self::whereIn('agent_id', [0, $agent_id])->orderBy('agent_id')->get()->keyBy('param_key')->toArray();
            $arr = [];
            if ($list) foreach ($list as $v) {
                $arr[$v['param_key']] = $v['param_value'];
            }
            return $arr;
        });
        return $param_key ? $rs[$param_key] : $rs;*/
        if ($param_key) return self::where('agent_id', $agent_id)->where('param_key', $param_key)->first()->param_value;
        return $list = self::where('agent_id', $agent_id)->get()->mapWithKeys(function ($item) {
            return [$item['param_key'] => $item['param_value']];
        });
    }

    /**
     *  未登陆时根据host获取代理商ID
     */
    public static function getAgentIdByHost()
    {
        $referer = parse_url(request()->header("referer"));
        $host = $referer['scheme'] . '://' . $referer['host'];
        return \Cache::remember('getAgentIdByHost' . $host, null, function () use ($host) {
            $rs = self::where('param_key', 'admin_domain')->where('param_value', 'like', $host.'%')->first();
            return $rs ? $rs->agent_id : 1;
        });
    }
}
