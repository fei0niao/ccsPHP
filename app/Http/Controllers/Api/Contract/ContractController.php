<?php

namespace App\Http\Controllers\Api\Contract;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Model\Contract;
use App\Http\Model\CustRisk;
use App\Http\Model\TradeFlow;
use App\Http\Model\Cust;
use App\Http\Controllers\Api\Feige;


/**
 * Class UMsgController 短信
 * @package App\Http\Controllers\Api
 */
class ContractController extends Controller
{
    use \App\Http\Controllers\Load\ShowBaseTrait;

    public static $model_name = 'Contract';

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

    public function count(Request $request)
    {
        $rs = [];
        if($request->SingleFreeze) {
            $rs['SingleFreeze'] = Contract::permission()->where('status',2)->count();
        }
        if($request->DoubleFreeze) {
            $rs['DoubleFreeze'] = Contract::permission()->where('status',3)->count();
        }
        return jsonReturn($rs);
    }

    public function create(Request $request)
    {
        try {
            \DB::beginTransaction();
            $agent_id = $this->agent_id;
            $contractParams = $request->contractParams;
            $cust_id = $contractParams['cust_id'];
            if (Contract::where([['cust_id', $cust_id], ['status', '<', 4]])->first()) {
                return failReturn('该客户已有在配合约！');
            }
            $contractParams['status'] = 1;//操盘中
            $contractParams['available_amount'] = $contractParams['current_finance_amount'];
            $contractParams['agent_id'] = $agent_id;
            $instance = Contract::create($contractParams);
            $stock_finance_id = $instance->id;
            $cust_id = $request->input('riskParams.cust_id');
            $account_remark ='开合约';
            $account_type = 1;
            $account_money = $available_amount = $contractParams['borrow_money'];
            TradeFlow::create(compact('cust_id', 'stock_finance_id', 'account_remark', 'account_type', 'available_amount', 'account_money', 'agent_id'));
            $account_type = 2;
            $account_money = $contractParams['current_finance_amount'] - $contractParams['borrow_money'];
            $available_amount =  $contractParams['current_finance_amount'];
            TradeFlow::create(compact('cust_id', 'stock_finance_id', 'account_remark', 'account_type', 'available_amount', 'account_money', 'agent_id'));
            $CustRisk = [];
            if ($stockRisk = $request->input('riskParams.stockRisk')) {
                $risk_control_type = '1';
                $risk_control_name = 'MAX_SINGLE_STOCK';
                $risk_control_description = '单股限买';
                foreach ($stockRisk as $v) {
                    if (!$v['stock_info']) continue;
                    $stock_info = explode(' ', $v['stock_info']);
                    $stock_code = $stock_info[0];
                    $stock_name = $stock_info[1];
                    $risk_control_value = (string)$v['value'];
                    $CustRisk[] = compact('stock_finance_id', 'cust_id', 'risk_control_type',
                        'stock_code', 'stock_name', 'risk_control_value', 'risk_control_name', 'risk_control_description', 'agent_id');
                }
            }
            if($CustRisk) CustRisk::insert($CustRisk);
            $CustRisk = [];
            $risk_control_type = '2';
            $risk_control_name = 'MAX_MAIN_STOCK';
            $risk_control_description = '主板限买';
            $risk_control_value = (string)$request->input('riskParams.mainBoardRisk');
            $CustRisk[] = compact('stock_finance_id', 'cust_id', 'risk_control_type', 'risk_control_value', 'risk_control_name', 'risk_control_description', 'agent_id');
            $risk_control_type = '2';
            $risk_control_name = 'MAX_SMALL_MEDIUM_STOCK';
            $risk_control_description = '中小板限买';
            $risk_control_value = (string)$request->input('riskParams.smallBoardRisk');
            $CustRisk[] = compact('stock_finance_id', 'cust_id', 'risk_control_type', 'risk_control_value', 'risk_control_name', 'risk_control_description', 'agent_id');
            $risk_control_type = '2';
            $risk_control_name = 'MAX_STARTUP_STOCK';
            $risk_control_description = '创业板限买';
            $risk_control_value = (string)$request->input('riskParams.secondBoardRisk');
            $CustRisk[] = compact('stock_finance_id', 'cust_id', 'risk_control_type', 'risk_control_value', 'risk_control_name', 'risk_control_description', 'agent_id');
            $risk_control_type = '3';
            $risk_control_name = 'MAX_SINGLE_MAIN_STOCK';
            $risk_control_description = '主板单票限买';
            $risk_control_value = (string)$request->input('riskParams.mainBoardSingleRisk');
            $CustRisk[] = compact('stock_finance_id', 'cust_id', 'risk_control_type', 'risk_control_value', 'risk_control_name', 'risk_control_description', 'agent_id');
            $risk_control_type = '3';
            $risk_control_name = 'MAX_SINGLE_SMALL_MEDIUM_STOCK';
            $risk_control_description = '中小板单票限买';
            $risk_control_value = (string)$request->input('riskParams.smallBoardSingleRisk');
            $CustRisk[] = compact('stock_finance_id', 'cust_id', 'risk_control_type', 'risk_control_value', 'risk_control_name', 'risk_control_description', 'agent_id');
            $risk_control_type = '3';
            $risk_control_name = 'MAX_SINGLE_STARTUP_STOCK';
            $risk_control_description = '创业板单票限买';
            $risk_control_value = (string)$request->input('riskParams.secondBoardSingleRisk');
            $CustRisk[] = compact('stock_finance_id', 'cust_id', 'risk_control_type', 'risk_control_value', 'risk_control_name', 'risk_control_description', 'agent_id');
            //一次性插入多条风控规则
            if($CustRisk) CustRisk::insert($CustRisk);
            \DB::commit();
            $cust = Cust::find($cust_id);
            $msg = '尊敬的客户，恭喜您的合约已开通成功！合约总金额为' .
                $contractParams['current_finance_amount'] . '元，其中借款额为' .
                $contractParams['borrow_money'] . '元。';
            (new Feige($cust,'createContract'))->send($msg);
            return successReturn('创建客户合约账户成功');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Log::info('创建客户合约账户', compact('msg'));
            return failReturn('创建客户合约账户失败');
        }
    }

    public function update($id, Request $request)
    {
        if (!$fills = $request->only('capital_pool_id', 'end_time', 'precautious_line_amount', 'liiquidation_line_amount', 'buy_commission_rate', 'sell_commission_rate', 'buy_stampduty_rate', 'sell_stampduty_rate', 'buy_transfer_rate', 'sell_transfer_rate', 'status')) {
            return failReturn('修改项未授权');
        }
        if (isset($fills['status']) && $fills['status'] == 4) $fills['stock_finance_settleup'] = date('Y-m-d H:i:s');
        $contract = Contract::find($id);
        $agent_id = $this->agent_id;
        try {
            \DB::beginTransaction();
            $borrow_money_offset = $request->borrow_money_offset;
            $cust_amount_offset = $request->cust_amount_offset;
            $available_amount_offset = $request->available_amount_offset;
            $cust_id = $contract->cust_id;
            $stock_finance_id = $contract->id;
            $account_remark = $request->remark;
            $available_amount = $contract->available_amount;
            $current_finance_amount = $contract->current_finance_amount;
            $msg = '';
            if ($cust_amount_offset) {
                $account_type = 21;
                $fills['available_amount'] = $available_amount += $cust_amount_offset;
                $fills['current_finance_amount'] = $current_finance_amount += $cust_amount_offset;
                $account_money = $cust_amount_offset;
                TradeFlow::create(compact('cust_id', 'stock_finance_id', 'account_remark', 'account_type', 'available_amount', 'account_money', 'agent_id'));
                $msg .= '您的合约个人金额' . ($cust_amount_offset > 0 ? '增加' : '减少') . abs($cust_amount_offset) .
                    '元，当前个人金额为' . ($contract->available_amount - $contract->borrow_money + $cust_amount_offset) . '元。';
            }
            if ($borrow_money_offset) {
                $account_type = 22;
                $fills['available_amount'] = $available_amount += $borrow_money_offset;
                $fills['current_finance_amount'] = $current_finance_amount += $borrow_money_offset;
                $fills['borrow_money'] = $contract->borrow_money + $borrow_money_offset;
                $account_money = $borrow_money_offset;
                TradeFlow::create(compact('cust_id', 'stock_finance_id', 'account_remark', 'account_type', 'available_amount', 'account_money', 'agent_id'));
                $msg .= '您的合约借款额' . ($borrow_money_offset > 0 ? '增加' : '减少') . abs($borrow_money_offset) . '元，当前借款额为' . $fills['borrow_money'] . '元。';
            }
            if ($available_amount_offset){
                $account_type = 23;
                $fills['available_amount'] = $available_amount += $available_amount_offset;
                $account_money = $available_amount_offset;
                TradeFlow::create(compact('cust_id', 'stock_finance_id', 'account_remark', 'account_type', 'available_amount', 'account_money', 'agent_id'));
                $msg .= '您的可用余额' . ($available_amount_offset > 0 ? '增加' : '减少') . abs($available_amount_offset) .
                    '元，当前可用余额为' . $fills['available_amount'] . '元。';
            }
            $contract->fill($fills)->save();
            if ($msg) {
                $msg = '尊敬的客户，' . $msg;
                $cust = Cust::find($cust_id);
                (new Feige($cust,'updateContract'))->send($msg);
            }
            \DB::commit();
            return successReturn('修改成功');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Log::info('修改客户合约账户失败', compact('msg'));
            return failReturn('修改失败');
        }
    }
}