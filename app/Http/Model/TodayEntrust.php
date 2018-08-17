<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class TodayEntrust extends Base
{
    protected $table = "u_stock_finance_entrust";

    protected $guarded = ['id', 'created_time', 'updated_time'];

    protected $roundFields = ['entrust_price', 'makedeal_average_price'];

    public static $ConfigAppends= [];

    public function __construct()
    {
        parent::__construct();
        //如果不是管理员
        if (Auth::user()->agent_id != 1) {
            $this->setHidden(
                []
            );
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
     * hasOne母账户委托
     */
    public function todayParentEntrust()
    {
        return $this->hasOne(TodayParentEntrust::Class, 'stock_finance_entrust_id', 'id');
    }

    /**
     * hasMany成交
     */
    public function todayDeal()
    {
        return $this->hasMany(TodayDeal::Class, 'stock_finance_entrust_id', 'id');
    }

    /**
     * hasMany成交
     */
    public function todayParentDeal()
    {
        return $this->hasMany(TodayParentDeal::Class, 'stock_finance_entrust_id', 'id');
    }

    /**
     *  子账户委托状态
     *
     * @param  string $value
     * @return string
     */
    public function getNewStockFinanceEntrustStatusAttribute()
    {
        switch ($this->attributes['stock_finance_entrust_status']) {
            case -1:
                return $this->attributes['cust_action'] == 3 ? '委托失败' : '委托等待返回';
            case 1:
                return $this->attributes['cust_action'] == 4 ? '委托撤销中' : '未成交';
            case 2:
                return $this->attributes['cust_action'] == 4 ? '委托撤销中' : '部分成交';
            case 3:
                return '部成部撤';
            case 4:
                return '已撤单';
            case 5:
                return '已成交';
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
    public function getNewSoldOrBuyAttribute()
    {
        switch ($this->attributes['sold_or_buy']) {
            case 1:
                return '买入';
            case 2:
                return '卖出';
            default:
                return '未知';
        }
    }
}
