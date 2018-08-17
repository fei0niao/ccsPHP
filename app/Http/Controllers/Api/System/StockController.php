<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockController extends Controller
{
    use \App\Http\Controllers\Load\ShowBaseTrait;

    public static $model_name = 'StockInfo';

    public function __construct()
    {
        parent::__construct();
        $this->middleware([]);
    }

    public function list(Request $request)
    {
        $rs = static::_run_orm($request->all());
        return jsonReturn($rs);
    }
}