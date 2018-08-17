<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class TradeFlow extends Base
{
    protected $table = "u_stock_financing_flow";

    protected $guarded = ['id', 'created_time', 'updated_time'];

    public static $ConfigAppends= [];

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        return $query->where('agent_id',$agent_id);
    }

    /**
     * belongsTo客户
     */
    public function cust()
    {
        return $this->belongsTo(Cust::Class, 'cust_id', 'id');
    }

    /**
     * belongsTo子账户
     */
    public function contract()
    {
        return $this->belongsTo(Contract::Class, 'stock_finance_id', 'id');
    }

    /**
     * belongsTo子账户今日委托
     */
    public function todayEntrust()
    {
        return $this->belongsTo(TodayEntrust::Class, 'entrust_id', 'id');
    }

    /**
     * belongsTo子账户历史委托
     */
    public function historyEntrust()
    {
        return $this->belongsTo(HistoryEntrust::Class, 'entrust_id', 'id');
    }

    /**
     *  记账类型
     *
     * @param  string $value
     * @return string
     */
    public function getNewAccountTypeAttribute()
    {
        switch ($this->attributes['account_type']) {
            case 1:
                return '期初借款额';
            case 2:
                return '期初客户金额';
            case 3:
                return '追加保证金';
            case 4:
                return '追配保证金';
            case 5:
                return '追加配资额';
            case 6:
                return '买入';
            case 7:
                return '买入';
            case 8:
                return '买入';
            case 9:
                return '买入';
            case 10:
                return '卖出';
            case 11:
                return '卖出';
            case 12:
                return '卖出';
            case 13:
                return '卖出';
            case 14:
                return '利润提取';
            case 15:
                return '配资结算';
            case 16:
                return '系统回收';
            case 17:
                return '系统补买';
            case 18:
                return '系统补卖';
            case 19:
                return '系统分配';
            case 20:
                return '股票除息';
            case 21:
                return '调整客户金额';
            case 22:
                return '调整借款额';
            case 23:
                return '调整可用余额';
            default:
                return '未知';
        }
    }

    /**
     *  子账户根据流水算的盈亏
     */
    public static function getWinLoss($stock_finance_id)
    {
        return round(self::where('stock_finance_id',$stock_finance_id)->sum('account_money'),3);
    }
}