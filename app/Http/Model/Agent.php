<?php

namespace App\Http\Model;

use Illuminate\Support\Facades\Auth;

class Agent extends Base
{
    public $timestamps = false;
    protected $table = "a_agent";
    protected $guarded = ['id'];
    public static $ConfigAppends= [];
}
