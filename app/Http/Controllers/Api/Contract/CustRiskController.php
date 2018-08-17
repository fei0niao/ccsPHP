<?php

namespace App\Http\Controllers\Api\Contract;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Model\CustRisk;

/**
 * Class UMsgController 短信
 * @package App\Http\Controllers\Api
 */
class CustRiskController extends Controller
{
    use \App\Http\Controllers\Load\ShowBaseTrait;

    public static $model_name = 'CustRisk';

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

    public function info($id, Request $request)
    {
        $rs = static::_run_orm($request->all(), $id);
        return jsonReturn($rs);
    }

    public function create(Request $request)
    {
        $fills = $request->all();
        $fills['agent_id'] = $this->agent_id;
        $rs = CustRisk::create($fills);
        if (!$rs) return failReturn('创建失败');
        return successReturn('创建成功');
    }

    public function update(Request $request)
    {
        try {
            \DB::beginTransaction();
            $cust_id = $request->input('cust_id');
            $stock_finance_id = $request->input('stock_finance_id');
            $stock_codes=[];
            if ($stockRisk = $request->input('stockRisk')) {
                $risk_control_name = 'MAX_SINGLE_STOCK';
                $risk_control_description = '单股限买';
                $risk_control_type = '1';
                foreach ($stockRisk as $v) {
                    if(!$v['stock_code']) continue;
                    $stock_codes[] = $v['stock_code'];
                    $stock_code = $v['stock_code'];
                    $stock_name = $v['stock_name'];
                    $risk_control_value = (string)$v['risk_control_value'];
                    CustRisk::updateOrCreate(compact('stock_finance_id', 'cust_id', 'risk_control_type','risk_control_name', 'stock_code','stock_name','risk_control_description'),compact('risk_control_value'));
                }
            }
            CustRisk::where(compact('stock_finance_id', 'cust_id', 'risk_control_name'))->whereNotIn('stock_code',$stock_codes)->delete();
            $risk_control_type = '2';
            $risk_control_name = 'MAX_MAIN_STOCK';
            $risk_control_description = '主板限买';
            $risk_control_value = (string)$request->input('mainBoardRisk');
            CustRisk::updateOrCreate(compact('stock_finance_id', 'cust_id', 'risk_control_type',  'risk_control_name','risk_control_description'),compact('risk_control_value'));
            $risk_control_type = '2';
            $risk_control_name = 'MAX_SMALL_MEDIUM_STOCK';
            $risk_control_description = '中小板限买';
            $risk_control_value = (string)$request->input('smallBoardRisk');
            CustRisk::updateOrCreate(compact('stock_finance_id', 'cust_id', 'risk_control_type', 'risk_control_name','risk_control_description'),compact('risk_control_value'));
            $risk_control_type = '2';
            $risk_control_name = 'MAX_STARTUP_STOCK';
            $risk_control_description = '创业板限买';
            $risk_control_value = (string)$request->input('secondBoardRisk');
            CustRisk::updateOrCreate(compact('stock_finance_id', 'cust_id',  'risk_control_type', 'risk_control_name','risk_control_description'),compact('risk_control_value'));
            $risk_control_type = '3';
            $risk_control_name = 'MAX_SINGLE_MAIN_STOCK';
            $risk_control_description = '主板单票限买';
            $risk_control_value = (string)$request->input('mainBoardSingleRisk');
            CustRisk::updateOrCreate(compact('stock_finance_id', 'cust_id', 'risk_control_type',  'risk_control_name', 'risk_control_description'),compact('risk_control_value'));
            $risk_control_type = '3';
            $risk_control_name = 'MAX_SINGLE_SMALL_MEDIUM_STOCK';
            $risk_control_description = '中小板单票限买';
            $risk_control_value = (string)$request->input('smallBoardSingleRisk');
            CustRisk::updateOrCreate(compact('stock_finance_id', 'cust_id', 'risk_control_type', 'risk_control_name', 'risk_control_description'),compact('risk_control_value'));
            $risk_control_type = '3';
            $risk_control_name = 'MAX_SINGLE_STARTUP_STOCK';
            $risk_control_description = '创业板单票限买';
            $risk_control_value = (string)$request->input('secondBoardSingleRisk');
            CustRisk::updateOrCreate(compact('stock_finance_id', 'cust_id', 'risk_control_type', 'risk_control_name', 'risk_control_description'),compact('risk_control_value'));
            \DB::commit();
            return successReturn('修改成功');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Log::info('修改失败', compact('msg'));
            return failReturn('修改失败');
        }
    }
}