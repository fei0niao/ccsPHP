<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get("/test", "TestController@test")->name('test');
Route::post("/oauth/token", "\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken")->middleware('throttle:200,1')->name('oauthToken');
Route::group([
    'prefix' => 'v1',
    'middleware' => ['api']
], function () {
    Route::post("/login", "Api\System\UserController@login")->name('login');//系统用户登录
    Route::post("/createCaptcha", "Api\System\CaptchaController@generateCaptcha")->name('createCaptcha');//创建验证码
});

Route::group([
    'prefix' => 'v1',
    'middleware' => ['api','auth:api','rolePermission']
], function () {
    Route::post("/uploadImg", "Api\OtherController@uploadImg")->name('uploadImg');//上传图片

    Route::post("/userList", "Api\System\UserController@list")->name('userList');//系统用户列表
    Route::post("/userInfo/{id}", "Api\System\UserController@info")->name('userInfo');//系统用户信息
    Route::post("/user/create", "Api\System\UserController@create")->name('userCreate');//创建系统用户
    Route::post("/user/update/{id}", "Api\System\UserController@update")->name('userUpdate');//修改系统用户
    Route::post("/user/rolePlay/{id}", "Api\System\UserController@rolePlay")->name('rolePlay');//角色扮演
    Route::post("/loginInfo", "Api\System\UserController@loginInfo")->name('loginInfo');//系统用户登录信息
    Route::post("/stockList", "Api\System\StockController@List")->name('stockList');//股票列表
    Route::post("/holidayList", "Api\System\HolidayController@list")->name('holidayList');//节假日列表

    Route::post("/agentList", "Api\System\AgentController@list")->name('agentList');//机构列表
    Route::post("/agentInfo/{id}", "Api\System\AgentController@info")->name('agentInfo');//机构信息
    Route::post("/agent/create", "Api\System\AgentController@create")->name('agentCreate');//创建机构
    Route::post("/agent/update/{id}", "Api\System\AgentController@update")->name('agentUpdate');//修改系统用户
    Route::post("/agentPermission/update/{agent_id}", "Api\System\AgentPermissionController@update")->name('agentPermissionUpdate');//修改机构权限范围

    Route::post("/systemRoleList", "Api\System\SystemRoleController@list")->name('roleList');//系统角色列表
    Route::post("/systemRoleInfo/{id}", "Api\System\SystemRoleController@info")->name('roleInfo');//系统角色信息
    Route::post("/systemRole/create", "Api\System\SystemRoleController@create")->name('roleCreate');//创建系统角色
    Route::post("/systemRole/update/{id}", "Api\System\SystemRoleController@update")->name('roleUpdate');//修改系统角色
    Route::post("/systemPermissionList", "Api\System\SystemPermissionController@list")->name('permissionList');//系统权限列表
    Route::post("/systemPermissionInfo/{id}", "Api\System\SystemPermissionController@info")->name('permissionInfo');//系统权限信息
    Route::post("/systemPermission/create", "Api\System\SystemPermissionController@create")->name('permissionCreate');//创建系统权限
    Route::post("/systemPermission/update/{id}", "Api\System\SystemPermissionController@update")->name('permissionUpdate');//修改系统权限
    Route::delete("/systemPermission/destroy/{id}", "Api\System\SystemPermissionController@destroy")->name('permissionDestroy');//删除系统权限

    Route::post("/systemRolePermissionList", "Api\System\SystemRolePermissionController@list")->name('rolePermissionList');//系统角色权限列表
    Route::post("/systemRolePermissionInfo/{id}", "Api\System\SystemRolePermissionController@info")->name('rolePermissionInfo');//系统角色权限信息
    Route::post("/systemRolePermission/update/{role_id}", "Api\System\SystemRolePermissionController@update")->name('rolePermissionUpdate');//修改系统角色权限信息

    Route::post("/systemParamList", "Api\System\SystemParamController@list")->name('systemParamList');//系统配置列表
    Route::post("/systemParamInfo/{id}", "Api\System\SystemParamController@info")->name('systemParamInfo');//系统角色信息
    Route::post("/systemParam/update/{id}", "Api\System\SystemParamController@update")->name('systemParamUpdate');//修改系统配置


    Route::post("/clientList", "Api\Customer\ClientController@list")->name('clientList');//客户列表
    Route::post("/clientInfo/{id}", "Api\Customer\ClientController@info")->name('clientInfo');//客户信息
    Route::post("/client/create", "Api\Customer\ClientController@create")->name('clientCreate');//创建客户
    Route::post("/client/update/{id}", "Api\Customer\ClientController@update")->name('clientUpdate');//修改客户
    Route::post("/msgList", "Api\Customer\MsgController@list")->name('msgList');//短信列表


    Route::post("/capitalPoolList", "Api\Contract\CapitalPoolController@list")->name('capitalPoolList');//资金池列表
    Route::post("/capitalPoolInfo/{id}", "Api\Contract\CapitalPoolController@info")->name('capitalPoolInfo');//资金池信息
    Route::post("/capitalPool/create", "Api\Contract\CapitalPoolController@create")->name('capitalPoolCreate');//创建资金池
    Route::post("/capitalPool/update/{id}", "Api\Contract\CapitalPoolController@update")->name('capitalPoolUpdate');//修改资金池
    Route::post("/contractList", "Api\Contract\ContractController@list")->name('contractList');//合约列表
    Route::post("/contractInfo/{id}", "Api\Contract\ContractController@info")->name('contractInfo');//合约信息
    Route::post("/contract/count", "Api\Contract\ContractController@count")->name('contractCount');//合约统计
    Route::post("/contract/create", "Api\Contract\ContractController@create")->name('contractCreate');//创建合约
    Route::post("/contract/update/{id}", "Api\Contract\ContractController@update")->name('contractUpdate');//修改合约
    Route::post("/parentAccountList", "Api\Contract\ParentAccountController@list")->name('parentAccountList');//母账户列表
    Route::post("/parentAccountInfo/{id}", "Api\Contract\ParentAccountController@info")->name('parentAccountInfo');//母账户信息
    Route::post("/parentAccount/create", "Api\Contract\ParentAccountController@create")->name('parentAccountCreate');//创建母账户
    Route::post("/parentAccount/update/{id}", "Api\Contract\ParentAccountController@update")->name('parentAccountUpdate');//修改母账户
    Route::post("/xrdrList", "Api\Contract\XrdrController@list")->name('xrdrList');//除权降息列表
    Route::post("/xrdrInfo/{id}", "Api\Contract\XrdrController@info")->name('xrdrInfo');//除权降息信息
    Route::post("/custRisk/create", "Api\Contract\CustRiskController@create")->name('custRiskCreate');//创建风控管理
    Route::post("/custRisk/update", "Api\Contract\CustRiskController@update")->name('custRiskUpdate');//修改风控管理 批量修改所以没有{id}


    Route::post("/todayEntrustList", "Api\Trade\TodayEntrustController@list")->name('todayEntrustList');//今日委托列表
    Route::post("/todayEntrustInfo/{id}", "Api\Trade\TodayEntrustController@info")->name('todayEntrustInfo');//今日委托信息
    Route::post("/todayParentEntrustList", "Api\Trade\TodayParentEntrustController@list")->name('todayParentEntrustList');//今日母账户委托列表
    Route::post("/todayParentEntrustInfo/{id}", "Api\Trade\TodayParentEntrustController@info")->name('todayParentEntrustInfo');//今日母账户委托信息
    Route::post("/todayDealList", "Api\Trade\TodayDealController@list")->name('todayDealList');//今日成交列表
    Route::post("/todayDealInfo/{id}", "Api\Trade\TodayDealController@info")->name('todayDealInfo');//今日成交信息
    Route::post("/historyEntrustList", "Api\Trade\HistoryEntrustController@list")->name('historyEntrustList');//历史委托列表
    Route::post("/historyEntrustInfo/{id}", "Api\Trade\HistoryEntrustController@info")->name('historyEntrustInfo');//历史委托信息
    Route::post("/historyParentEntrustList", "Api\Trade\HistoryParentEntrustController@list")->name('historyParentEntrustList');//历史母账户委托列表
    Route::post("/historyParentEntrustInfo/{id}", "Api\Trade\HistoryParentEntrustController@info")->name('historyParentEntrustInfo');//历史母账户委托信息
    Route::post("/historyDealList", "Api\Trade\HistoryDealController@list")->name('historyDealList');//历史成交列表
    Route::post("/historyDealInfo/{id}", "Api\Trade\HistoryDealController@info")->name('historyDealInfo');//历史成交信息
    Route::post("/tradeFlowList", "Api\Trade\TradeFlowController@list")->name('tradeFlowList');//交易流水列表
    Route::post("/tradeFlowInfo/{id}", "Api\Trade\TradeFlowController@info")->name('tradeFlowInfo');//交易流水信息
    Route::post("/holdingList", "Api\Trade\HoldingController@list")->name('holdingList');//持仓列表
    Route::post("/holdingInfo/{id}", "Api\Trade\HoldingController@info")->name('holdingInfo');//持仓信息
    Route::post("/riskLogList", "Api\Trade\RiskLogController@list")->name('riskLogList');//风控日志列表
    Route::post("/riskLogInfo/{id}", "Api\Trade\RiskLogController@info")->name('riskLogInfo');//风控日志信息
    Route::post("/tradeFeeList", "Api\Trade\TradeFeeController@list")->name('tradeFeeList');//手续费列表(佣金)
    Route::post("/tradeFeeInfo/{id}", "Api\Trade\TradeFeeController@info")->name('tradeFeeInfo');//手续费信息(佣金)
    Route::post("/platformFlowList", "Api\Trade\PlatformFlowController@list")->name('platformFlowList');//平台流水列表(佣金)
    Route::post("/platformFlowInfo/{id}", "Api\Trade\PlatformFlowController@info")->name('platformFlowInfo');//平台流水信息(佣金)

    Route::post("/requestApi/getCustAccessToken", "Api\RequestApiController@getCustAccessToken")->name('getCustAccessToken');//获取用户token
    Route::post("/requestApi/eveningUp", "Api\RequestApiController@eveningUp")->name('eveningUp');//一键平仓
    Route::post("/requestApi/settleUp", "Api\RequestApiController@settleUp")->name('settleUp');//结算
    Route::post("/requestApi/perEveningUp", "Api\RequestApiController@perEveningUp")->name('perEveningUp');//分笔平仓
    Route::post("/requestApi/batchDeliveryStock", "Api\RequestApiController@batchDeliveryStock")->name('batchDeliveryStock');//手动批量派股
    Route::post("/requestApi/batchStockRegister", "Api\RequestApiController@batchStockRegister")->name('batchStockRegister');//手动批量除权登记
    Route::post("/requestApi/doXrdr", "Api\RequestApiController@doXrdr")->name('doXrdr');//手动除权除息
    Route::post("/requestApi/adjustBalance", "Api\RequestApiController@adjustBalance")->name('adjustBalance');//调整余额
    Route::post("/requestApi/retrieveStock", "Api\RequestApiController@retrieveStock")->name('retrieveStock');//回收股票
    Route::post("/requestApi/assignStock", "Api\RequestApiController@assignStock")->name('assignStock');//分配股票
    Route::post("/requestApi/assignBuyDayDeal", "Api\RequestApiController@assignBuyDayDeal")->name('assignBuyDayDeal');//买入当日关联
    Route::post("/requestApi/assignBuyHistoryDeal", "Api\RequestApiController@assignBuyHistoryDeal")->name('assignBuyHistoryDeal');//买入历史关联
    Route::post("/requestApi/assignSellDayDeal", "Api\RequestApiController@assignSellDayDeal")->name('assignSellDayDeal');//卖出当日关联
    Route::post("/requestApi/assignSellHistoryDeal", "Api\RequestApiController@assignSellHistoryDeal")->name('assignSellHistoryDeal');//卖出历史关联
});
Route::get('/login', function () {
    return 'not exist';
})->name('login');

//防止别人访问不存在的接口报错
Route::any('/{any}', function () {
    return 'not exist';
})->where('any', '^.*$')->name('any');