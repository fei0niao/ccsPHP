<?php

namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * App\Http\Model\Base
 *
 * @mixin \Eloquent
 */

class Base extends Model
{
    public static $ConfigAppends= [];

    protected $roundFields = []; //数据过滤

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->appends = static::$ConfigAppends; //用self会有问题 不会用子类的$ConfigAppends属性
    }

    //增加新的计算属性
    public function getMutatedAttributes()
    {
        $attributes = parent::getMutatedAttributes();
        return array_merge($attributes, $this->roundFields);
    }

    //定义新的计算属性处理方式
    protected function mutateAttributeForArray($key, $value)
    {
        if(in_array($key, $this->roundFields)){
            return round($value,3);
        }
        return parent::mutateAttributeForArray($key, $value);
    }

    //模型查询权限控制 应再继承的各个子模型单独配置
    public static function permission($query = ''){
        return $query?:static::query();
    }

    public static function boot()
    {
        parent::boot();
        $name = substr(get_called_class(), (int)strrpos(get_called_class(), '\\') + 1);
        self::creating(function ($model) {
        });
        self::created(function ($model) use ($name) {
            if (substr($name,-3) == 'Log') return;//关键 日志表模型不能有日志 否则会产生多条记录
            static::operate_log($model, '创建' . $name);
        });
        self::updating(function ($model) {
        });
        self::updated(function ($model) use ($name) {
            static::operate_log($model, '更新' . $name, 1);
        });
        self::deleting(function ($model) {
        });
        self::deleted(function ($model) use ($name) {
            self::operate_log($model, '删除' . $name);
        });
    }

    //写日志 flag=0时为创建或其他 flag=1时为更新 以后可扩展flag=2时为删除只记录删除id
    protected static function operate_log($model, $remark, $flag = 0)
    {
        $user = Auth::user();
        $params = [
            'url' => request()->url(),
            'ip' => request()->ip(),
            'sys_user' => $user->id,
            'sys_user_name' => $user->name,
            'role_id' => $user->role_id,
            'remark' => $remark,
            'agent_id' => $user->agent_id
        ];
        if ($flag) {
            //原参数
            $original_params = $model->original;//array_diff_assoc($model->original, $model->attributes);
            //请求参数
            $request_params = request()->all();//array_diff_assoc($model->attributes, $model->original);
            $params['request_params'] = str_replace('\\', '', json_encode($request_params));
            $params['original_params'] = str_replace('\\', '', json_encode($original_params));
        } else {
            $params['request_params'] = str_replace('\\', '', json_encode($model->attributes));
        }
        SystemOperateLog::create($params);
    }

    const CREATED_AT = "created_time";
    const UPDATED_AT = "updated_time";
}