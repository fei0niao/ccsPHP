<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class HistoryDeal extends Base
{
    protected $table = "u_stock_finance_day_makedeal_history";
    protected $guarded = ['id', 'created_time', 'updated_time'];
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
     * belongsTo子账户委托
     */
    public function historyEntrust()
    {
        return $this->belongsTo(HistoryEntrust::Class, 'stock_finance_entrust_id', 'id');
    }

    /**
     * belongsTo母账户委托
     */
    public function historyParentEntrust()
    {
        return $this->belongsTo(HistoryParentEntrust::Class, 'parent_entrust_id', 'id');
    }

    /**
     * belongsTo母账户成交
     */
    public function historyParentDeal()
    {
        return $this->belongsTo(HistoryParentDeal::Class, 'parent_makedeal_id', 'id');
    }

    /**
     * 成交日期时间
     */
    public function getNewMakedealDateTimeAttribute()
    {
        return $this->attributes['makedeal_date'] . " " . $this->attributes['makedeal_time'];
    }

    /**
     *  成交状态
     *
     * @param  string $value
     * @return string
     */
    public function getNewMakeDealStatusAttribute()
    {
        switch ($this->attributes['make_deal_status']) {
            case 5:
                return '全部成交';
            case 2:
                return '部分成交';
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
