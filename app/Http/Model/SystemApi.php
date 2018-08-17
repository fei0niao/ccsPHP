<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class SystemApi extends Base
{
    protected $table = "s_system_api";
    protected $guarded = [];
    public $timestamps = false;
    public static $ConfigAppends = [];

    //权限
    public static function permission($query = '')
    {
        return $query?:static::query();
    }
}