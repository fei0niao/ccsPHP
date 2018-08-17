<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class Employee extends Base
{
    protected $table = "a_agent_emp";
    protected $guarded = ['id', 'create_time', 'updated_time'];
    protected $hidden = ['password'];
    public static $ConfigAppends= [];

    //权限
    public static function permission($query = '')
    {
        $query = $query?:self::query();
        $agent_id = Auth::user()->agent_id;
        return $query->where('agent_id',$agent_id);
    }

    public function agent()
    {
        return $this->belongsTo('App\Http\Model\Agent');
    }

    public function role()
    {
        return $this->belongsTo('App\Http\Model\Role');
    }
}
