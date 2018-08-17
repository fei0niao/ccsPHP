<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class RiskLog extends Base
{
    protected $table = "s_stock_finance_risk_control_log";

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
     * 获取状态
     *
     * @param  string $value
     * @return string
     */
    public function getNewRiskControlTypeAttribute()
    {
        switch ($this->attributes['risk_control_type']) {
            case 1:
                return '付息欠费';
                break;
            case 2:
                return '超预警线';
                break;
            case 3:
                return '超平仓线';
                break;
            case 4:
                return '补保失败';
                break;
            case 5:
                return '试用账户自动平仓失败';
                break;
            case 6:
                return '试用账户自动结算失败';
                break;
            default:
                return '未知状态';

        }
    }

}
