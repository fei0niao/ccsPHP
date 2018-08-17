<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class Cust extends Base
{
    protected $table = "u_customer";
    public $timestamps = false;
    protected $guarded = ['id', 'created_time', 'updated_time'];
    protected $hidden = ['withdraw_pw', 'password'];
    public static $ConfigAppends= [];

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        return $query->where('agent_id',$agent_id);
    }

    /**
     * new_is_login_forbidden
     * @return string
     */
    public function getNewIsLoginForbiddenAttribute()
    {
        switch ($this->attributes['is_login_forbidden']) {
            case 0:
                return '正常';
            case 1:
                return '禁止';
            default:
                return '未知';
        }
    }

    public function getCellphoneAttribute($value)
    {
        return SystemParam::getParamValue('hash_cellphone')?substr($value,0,3).'****'.substr($value,-4):$value;
    }

    public function contract()
    {
        return $this->hasMany(Contract::Class, 'cust_id', 'id');
    }

    public function getNewCustInfoAttribute()
    {
        $phone = SystemParam::getParamValue('hash_cellphone')?substr($this->attributes['cellphone'],0,3) . '*****' . substr($this->attributes['cellphone'],-3):$this->attributes['cellphone'];
        return $this->attributes['id'] . '-' . $this->attributes['real_name'] . ' ' . $phone;
    }

    /**
     *  是否有合约
     *
     * @param  string $value
     * @return string
     */
    public function getNewHasContractAttribute()
    {
        return $this->relations['contract']->isEmpty()?'无':'有';
    }
}