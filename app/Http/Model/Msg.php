<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

/**
 * 用户短信记录
 * App\Http\Model\UMsg
 *
 */
class Msg extends Base
{
    public $timestamps = false;
    protected $table = "u_msg";
    protected $guarded = ['id'];
    public static $ConfigAppends= [];

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        return $query->where('agent_id',$agent_id);
    }

    public function cust()
    {
        return $this->belongsTo(Cust::class, 'cust_id', 'id');
    }


    public function getCellphoneAttribute($value)
    {
        return SystemParam::getParamValue('hash_cellphone')?substr($value,0,3).'****'.substr($value,-4):$value;
    }

    /**
     * status
     * @return string
     */
    public function getNewStatusAttribute()
    {
        switch ($this->attributes['status']) {
            case 0:
                return '失败';
            case 1:
                return '成功';
            default:
                return '未知';
        }
    }

    /**
     * status
     * @return string
     */
    public function getNewMsgTypeAttribute()
    {
        switch ($this->attributes['msg_type']) {
            case 1:
                return '实名认证';
            case 2:
                return '注册成功';
            case 3:
                return '开合约';
            case 4:
                return '调整合约金额';
            case 5:
                return '结算';
            case 6:
                return '充值';
            case 7:
                return '提现';
            case 8:
                return '风控';
            case 9:
                return '验证码';
            case 10:
                return '提取利润';
            default:
                return '未知';
        }
    }
}
