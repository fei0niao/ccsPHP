<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class TradeFee extends Base
{
    protected $table = "u_stock_finance_fee_report";

    protected $guarded = ['id', 'created_time', 'updated_time'];

    protected $roundFields = ['bargin_price', 'commison', 'transfer_fee', 'stamp_duty'];

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


    public function historyParentDeal()
    {
        return $this->belongsTo(HistoryParentDeal::Class, 'parent_makedeal_id', 'id');
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
