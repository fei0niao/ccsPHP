<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class HistoryParentEntrust extends Base
{
    protected $table = "u_parent_stock_finance_entrust_history";

    protected $guarded = ['id', 'created_time', 'updated_time'];

    protected $roundFields = ['bargain_average_price'];

    public static $ConfigAppends= [];

    public function __construct()
    {
        parent::__construct();
        //如果不是管理员
        if (Auth::user()->agent_id != 1) {
        }
    }

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        return $query->where('agent_id',$agent_id);
    }

    /**
     *  母账户委托状态
     *
     * @param  string $value
     * @return string
     */
    public function getNewParentEntrustStatusAttribute()
    {
        switch ($this->attributes['parent_entrust_status']) {
            case 1:
                return '未成交';
            case 2:
                return '部分成交';
            case 3:
                return '部成部撤';
            case 4:
                return '已撤单';
            case 5:
                return '已成交';
            case 6:
                return '委托失败';
            default:
                return '未知';
        }
    }

    /**
     *  买卖方向
     *
     * @param  string $value
     * @return string
     */
    public function getNewSellBuyAttribute()
    {
        switch ($this->attributes['sell_buy']) {
            case 1:
                return '买入';
            case 2:
                return '卖出';
            default:
                return '未知';
        }
    }
}
