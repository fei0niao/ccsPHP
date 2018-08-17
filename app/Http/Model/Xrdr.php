<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class Xrdr extends Base
{
    protected $table = "s_xr_dr_info";

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
     *  状态
     *
     * @param  string $value
     * @return string
     */
    public function getXrDrStatusAttribute($value)
    {
        switch ($value) {
            case 0:
                return '未分配';
            case 1:
                return '已分配';
            default:
                return '未知';
        }
    }
}
