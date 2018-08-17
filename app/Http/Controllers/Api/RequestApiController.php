<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Model\Cust;

/**
 * Class JavaApiController Java交互控制器
 * @package App\Http\Controllers\Api
 */
class RequestApiController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware([]);
    }

    public function getCustAccessToken(Request $request){
        $uri = config('env.JAVA_API_URL').'/finances-center/noauth/api/trade/1.0/getActCustToken';
        $cust = Cust::find($request->custId);
        $params = [
            'custId' => $request->custId,
            'password' => opensslEncode($cust->password)
        ];
        return guzzleRequest($uri, $params,'json');
    }

    public function updateParentStockActPwd($id,$data){
        $uri = config('env.JAVA_API_URL').'/finances-center/noauth/api/trade/1.0/updateParentStockPwd';
        $params = [
            'parentStockFinanceId' => $id,
            'account' => opensslEncode($data['account']),
            'password' => $data['password'],
            'communicationPw' => $data['communication_pw']
        ];
        return guzzleRequest($uri, $params);
    }

    //子账户一键平仓
    public function eveningUp(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/stockfinance/eveningup';
        $params = [
            'stockFinanceId' => $request->id,
            'entrustIp' => $request->ip()
        ];
        return guzzleRequest($uri, $params);
    }

    //结算
    public function settleUp(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/stockfinance/settleup';
        $params = [
            'stockFinanceId' => $request->id
        ];
        return guzzleRequest($uri, $params);
    }

    //子账户分笔平仓
    public function perEveningUp(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/stockfinance/eveningupPerHolding';
        $params = [
            'id' => $request->id,
            'entrustIp' => $request->ip()
        ];
        return guzzleRequest($uri, $params);
    }

    //手动批量派股
    public function batchDeliveryStock(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/batchDeliveryStock';
        return guzzleRequest($uri);
    }

    //手动批量登记
    public function batchStockRegister(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/batchStockRightRegister';
        return guzzleRequest($uri);
    }

    //指定除权除息
    public function doXrdr(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/doXrdr';
        $params = [
            'id' => $request->id,
        ];
        return guzzleRequest($uri, $params);
    }

    //调母账户余额
    public function adjustBalance(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/parentStockFinance/adjustBalance';
        $params = [
            'parentStockFinanceId' => $request->id,
            'offsetAmount' => $request->offset_amount,
        ];
        return guzzleRequest($uri, $params);
    }

    //回收股票
    public function retrieveStock(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/retrieveStock';
        $params = $request->only('stockFinanceHoldId', 'marketPrice');
        $params['retrievePercentage'] = 1;//回收股票默认为100%
        return guzzleRequest($uri, $params);
    }

    //回收股票进行分配
    public function assignStock(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/assignStock';
        $params = $request->only('stockFinanceHoldId', 'marketPrice', 'assignQuantity', 'stockFinanceId');
        return guzzleRequest($uri, $params);
    }

    //买入交易关联当日子账户
    public function assignBuyDayDeal(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/assignBuyDayMakeDeal';
        $params = [
            'id' => $request->id,
            'stockFinanceEntrustId' => $request->stockFinanceEntrustId
        ];
        return guzzleRequest($uri, $params);
    }

    //买入交易关联历史子账户
    public function assignBuyHistoryDeal(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/assignBuyHistoryMakeDeal';
        $params = [
            'id' => $request->id,
            'stockFinanceEntrustId' => $request->stockFinanceEntrustId
        ];
        return guzzleRequest($uri, $params);
    }

    //卖出交易关联当日子账户
    public function assignSellDayDeal(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/assignSellDayMakeDeal';
        $params = [
            'id' => $request->id,
            'stockFinanceId' => $request->stockFinanceId,
            'remark' => $request->remark
        ];
        return guzzleRequest($uri, $params);
    }

    //卖出交易关联历史子账户
    public function assignSellHistoryDeal(Request $request)
    {
        $uri = config('env.JAVA_API_URL').'/finances-center/backend/api/1.0/assignSellHistoryMakeDeal';
        $params = [
            'id' => $request->id,
            'stockFinanceId' => $request->stockFinanceId,
            'remark' => $request->remark
        ];
        return guzzleRequest($uri, $params);
    }
}