<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;

class ParentAccount extends Base
{
    protected $table = "s_parent_stock_finance";

    protected $guarded = ['id', 'created_time', 'updated_time'];

    protected $casts = [

    ];

    public static $ConfigAppends= [];

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        return $query->where('agent_id',$agent_id);
    }

    public function capitalPool()
    {
        return $this->belongsTo(CapitalPool::class, 'capital_id', 'id');
    }

    /**
     *  账户状态
     *
     * @param  string $value account_status
     * @return string
     */
    public function getNewAccountStatusAttribute()
    {
        switch ($this->attributes['account_status']) {
            case 1:
                return '操盘中';
            case 2:
                return '单向冻结';
            case 3:
                return '双向冻结';
            default:
                return '未知';
        }
    }


    /**
     *  登录状态 new_login_status
     *
     * @param  string $value account_status
     * @return string
     */
    public function getNewLoginStatusAttribute()
    {
        $value = Redis::hGet('parentStock', $this->attributes['id']);
        switch ($value) {
            case 0:
                return '未登录';
            case 1:
                return '正常';
            default:
                return '未知';
        }
    }

    /**
     * 母账户总资金
     */
    public function getNewTotalCapitalAttribute()
    {
        return $this->attributes['available_capital'] + $this->attributes['freezn_capital'];
    }
}
