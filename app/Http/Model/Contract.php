<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class Contract extends Base
{
    protected $table = "u_stock_financing";

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

    public function custRisk()
    {
        return $this->hasMany(CustRisk::Class, 'stock_finance_id', 'id');
    }

    public function getNewCustRiskAttribute()
    {
        $this->setHidden(['custRisk']);
        $custRisk = $this->relations['custRisk']->groupBy('risk_control_name')->toArray();
        return [
            'stockRisk' => $custRisk['MAX_SINGLE_STOCK']??[],
            'mainBoardRisk' => $custRisk['MAX_MAIN_STOCK'][0]['risk_control_value']??'',
            'smallBoardRisk' => $custRisk['MAX_SMALL_MEDIUM_STOCK'][0]['risk_control_value']??'',
            'secondBoardRisk' => $custRisk['MAX_STARTUP_STOCK'][0]['risk_control_value']??'',
            'mainBoardSingleRisk' => $custRisk['MAX_SINGLE_MAIN_STOCK'][0]['risk_control_value']??'',
            'smallBoardSingleRisk' => $custRisk['MAX_SINGLE_SMALL_MEDIUM_STOCK'][0]['risk_control_value']??'',
            'secondBoardSingleRisk' => $custRisk['MAX_SINGLE_STARTUP_STOCK'][0]['risk_control_value']??''
        ];
    }

    /**
     *  状态
     *
     * @param  string $value
     * @return string
     */
    public function getNewStatusAttribute()
    {
        switch ($this->attributes['status']) {
            case 1:
                return '操盘中';
            case 2:
                return '单向冻结';
            case 3:
                return '双向冻结';
            case 4:
                return '已关闭';
            default:
                return '未知';
        }
    }


    /**
     * 获取合约动态资产信息
     * @return array
     */
    public function getNewMarketAttribute()
    {
        $arr['marketValue'] = $marketValue = Holding::getTotalHoldingMarketValue($this->attributes['id']);
        $arr['totalAssert'] = $marketValue + $this->attributes['available_amount']
            + $this->attributes['freeze_buying_money'] + $this->attributes['freeze_charge_money'];
        $arr['netAsserts'] = $arr['totalAssert'] - $this->attributes['borrow_money'];
        $arr['winLoss'] = $arr['totalAssert'] - $this->attributes['current_finance_amount'];
        $arr['holdingRate'] = !empty($arr['totalAssert'])? ($marketValue / $arr['totalAssert']) : 0;
        $arr['precautiousGap'] = $arr['totalAssert'] - $this->attributes['precautious_line_amount'];
        $arr['liquidationGap'] = $arr['totalAssert'] - $this->attributes['liiquidation_line_amount'];
        return formatAll($arr);
    }

    /**
     * 获取结算合约盈亏信息
     * @return array
     */
    public function getNewWinlossAttribute()
    {
        $winLoss = TradeFlow::getWinLoss($this->attributes['id']);
        return $winLoss;
    }
}