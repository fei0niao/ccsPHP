<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;

class StockInfo extends Base
{
    use SoftDeletes;

    protected $table = "s_stock_info";

    protected $guarded = ['id', 'deleted_at', 'created_time', 'updated_time'];

    protected $dates = ['deleted_at'];

    public static $ConfigAppends= [];


    public function getNewIsFollowParamAttribute()
    {
        switch ($this->attributes['is_follow_param']) {
            case 0:
                return '否';
            case 1:
                return '是';
            default:
                return '未知';
        }
    }

    public function getNewStockCategoryAttribute()
    {
        switch ($this->attributes['stock_category']) {
            case 1:
                return '上证';
            case 2:
                return '深证';
            default:
                return '未知';
        }
    }

    public function getNewIsStockEnableAttribute()
    {
        switch ($this->attributes['is_stock_enable']) {
            case 0:
                return '否';
            case 1:
                return '是';
            default:
                return '未知';
        }
    }

    public function getNewHaltStatusAttribute()
    {
        return $this->attributes['halt_status'] ? '停牌' : '正常';
    }

    public function getNewStockCodeNameAttribute()
    {
        return $this->attributes['stock_code'] . ' ' . $this->attributes['stock_name'];
    }

    /**
     * 获取这个股票的信息
     * @return mixed
     */
    public function getNewStockInfoAttribute()
    {
        return $this->attributes['new_stock_info'] = json_decode(Redis::hGet("stockmarket", $this->attributes['stock_code']), true);
    }
}
