<?php

namespace App\Http\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;
    const CREATED_AT = "created_time";
    const UPDATED_AT = "updated_time";

    protected $table = "a_agent_emp";
    protected $guarded = ['id', 'create_time', 'updated_time'];
    protected $hidden = ['password'];
    public static $ConfigAppends= [];

    public function __construct()
    {
        parent::__construct();
        $this->appends = self::$ConfigAppends;
    }

    //模型查询权限控制 应再继承的各个子模型单独配置
    public static function permission($query = ''){
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        if ($agent_id == 1)  return $query;
        return $query->where('agent_id',$agent_id);
    }

    //belongs to角色
    public function role()
    {
        return $this->belongsTo(Role::class,'role_id','id');
    }

    //hasmany 登录日志
    public function loginLog()
    {
        return $this->hasMany(LoginLog::class,'sys_user_id','id');
    }

    //新加字段
    public function  getNewIsForbidAttribute(){
        switch ($this->attributes['is_forbid']) {
            case 0:
                return '正常';
            case 1:
                return '禁用';
            default:
                return '未知';
        }
    }

    //自定义 oauth passport 登陆用户名 id 可以改成其他字段
    public function findForPassport($username)
    {
        $agent_id = getAgentID();
        $user = $this->where('name', $username)->where('agent_id',$agent_id)->where('is_forbid', 0)->first();
        return $user;
    }

    //自定义 oauth passport 验证密码
    public function validateForPassportPasswordGrant($password)
    {
        return Hash::check($password, $this->password);
    }

    public function AauthAcessToken()
    {
        return $this->hasMany('\App\OauthAccessToken');
    }


    public function agent()
    {
        return $this->belongsTo('App\Http\Model\Agent');
    }

    /*public function getPhoneAttribute($value)
    {
        return SystemParam::getParamValue('hash_cellphone')?substr($value,0,3).'****'.substr($value,-4):$value;
    }*/
}
