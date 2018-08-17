<?php

use App\Http\Model\RequestApiLog;
use Lcobucci\JWT\Parser;
use Illuminate\Support\Facades\Redis;
use OSS\OssClient;
use OSS\Core\OssException;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Model\SystemParam;


/**
 * @param array $data 返回json 数据体
 * @param int $code_status 返回 状态
 * @param string $message 消息
 * @param \Illuminate\Http\Request|null $request 请求 用于debug
 * @return \Illuminate\Http\JsonResponse  json返回
 */
if (!function_exists("jsonReturn")) {
    function jsonReturn($data = [], int $code_status = 1, string $message = '', int $httpStatusCode = 200)
    {
        $json['status'] = $code_status ? $code_status : 0;
        $json['data'] = $data;
        $json['msg'] = $message;
        if (config('app.debug')) {
            $json['debug_sql'] = DB::getQueryLog();
        }
        return response()->json($json, $httpStatusCode);
    }
}

/**
 * 失败Return
 */
if (!function_exists("failReturn")) {
    function failReturn($message = '')
    {
        return jsonReturn([], 0, $message);
    }
}

/*
 *  成功Return
 */
if (!function_exists("successReturn")) {
    function successReturn($message = '')
    {
        return jsonReturn([], 1, $message);
    }
}

/**
 * 适用于直接输出 不适合return的情况 默认是错误 并中止执行
 */
if (!function_exists("dieAndEcho")) {
    function dieAndEcho(string $message = '')
    {
        $json['status'] = 0;
        $json['data'] = [];
        $json['msg'] = $message;
        header("HTTP/1.0 200");
        echo json_encode($json);
        die;
    }
}

/**
 * openssl加密
 */
if (!function_exists("opensslEncode")) {
    function opensslEncode($str)
    {
        // 获取公匙
        $pub_key = openssl_get_publickey(file_get_contents(storage_path('rsa_public_key.pem')));
        $encrypted = '';
        openssl_public_encrypt($str, $encrypted, $pub_key);
        return base64_encode($encrypted);
    }
}

/**
 * openssl解密
 */
if (!function_exists("opensslDecode")) {
    function opensslDecode($str)
    {
        // 获取私匙
        $pri_key = openssl_get_privatekey(file_get_contents(storage_path('pkcs8_rsa_private_key.pem')));
        $decrypted = '';
        openssl_private_decrypt(base64_decode($str), $decrypted, $pri_key);
        return $decrypted;
    }
}


/**
 * 登录
 */
if (!function_exists("apiLogin")) {
    function apiLogin($username, $password)
    {
        request()->request->add([
            'grant_type' => "password",
            'client_id' => config('env.CLIENT_ID'),
            'client_secret' => config('env.CLIENT_SECRET'),
            'username' => $username,
            'password' => $password,
            'scope' => ''
        ]);
        $proxy = Request::create(
            'oauth/token',
            'POST'
        );
        $ret = json_decode(\Route::dispatch($proxy)->getContent(), true);
        if ($ret && isset($ret['access_token'])) {
            return $ret;
        }
        return false;
    }
}

/**
 * 获取ip信息，调淘宝ip接口
 */
if (!function_exists("getIpInfo")) {
    function getIpInfo($ip)
    {
        if (in_array($ip, ['::1', '127.0.0.1', 'localhost'])) {
            return '内网IP';
        }
        $client = new \GuzzleHttp\Client();
        try {
            $res = $client->request("GET", "http://ip.taobao.com/service/getIpInfo.php?ip={$ip}", [
                'connect_timeout' => 2,'timeout' => 6
            ]);
            $res = json_decode($res->getBody(), true);
            if ($res["code"] != 0) {
                return '未知';
            }
            return $res["data"]["country"] . $res["data"]["region"] .
                $res["data"]["city"] . $res["data"]["isp"];
        } catch (\Exception $e) {
            return '未知';
        }
    }
}


/**
 * 解析登录token
 */
if (!function_exists("parsePassportAuthorization")) {
    function parsePassportAuthorization($request)
    {
        $authorization = $request->header("Authorization");
        $jwt = trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $authorization));
        try {
            $token = (new Parser())->parse($jwt);
            $data = [
                "sub" => $token->getClaim("sub"),   //用户id
                "jti" => $token->getClaim("jti"),   //加密token值
                //要其他数据自己取
            ];
        } catch (\Exception $e) {
            return false;
        }

        return $data;
    }
}

/**
 * 密码加密
 */
if (!function_exists("encryptPassword")) {
    function encryptPassword($password)
    {
        return md5(md5(md5(md5($password))));
    }
}


/**
 * 隐藏字符串中间部分
 */
if (!function_exists("half_replace")) {
    function half_replace($str)
    {
        $len = ceil(mb_strlen($str, "utf8") / 2);
        $prefix = mb_substr($str, 0, ceil($len / 2), "utf8");
        $suffix = mb_substr($str, $len + ceil($len / 2), null, "utf8");
        return $prefix . str_repeat("*", $len) . $suffix;
    }
}

/**
 * 获取股票行情
 */
if (!function_exists("getStockInfo")) {
    function getStockInfo($code, $isStrict = false)
    {
        $name = "stockmarket";                  //股票池
        $haltName = "tradingHaltStock";         //停盘信息
        $keys = Redis::hkeys($name);
        $keyList = [];
        $i = 0; //数量限制
        foreach ($keys as $v) {
            if ($isStrict) {
                if ((string)$v === (string)$code) $keyList[] = $v;
            } else {
                if ($i > 4) break;
                if (strpos($v, (string)$code) !== false) {
                    $keyList[] = $v;
                    $i++;
                }
            }
        }

        $dataList = $keyList ? Redis::hmGet($name, $keyList) : [];
        $haltList = $keyList ? Redis::hmGet($haltName, $keyList) : [];
        $newData = [];
        $newHaltData = [];
        array_walk($dataList, function ($v, $k) use (&$newData) {
            if ($t = json_decode($v, true)) {
                //无市价时，取停市价
                $t["price"] = $t["price"] ?: $t["preClose"];
                $newData[] = $t;
            }
        });

        array_walk($haltList, function ($v, $k) use (&$newHaltData) {
            if ($t = json_decode($v, true)) {
                $newHaltData[$t["stockCode"]] = $t;
            }
        });

        array_walk($newData, function ($v, $k) use (&$newData, $newHaltData) {
            $newData[$k]["haltStatus"] = isset($newHaltData[$v["code"]]) ? $newHaltData[$v["code"]]["haltStatus"]
                : 0;
        });

        return $isStrict ? ($newData[0] ?? null) : $newData;
    }
}

/**
 * 发送请求，type=form_params,json
 */
if (!function_exists("guzzleRequest")) {
    function guzzleRequest($url, $data = [], $type = 'form_params')
    {
        $client = new \GuzzleHttp\Client();
        $backApiSecret = Redis::get('backApiSecret');
        $secret = $backApiSecret??'ktsd-orgqwer!@#';
        $options = [$type => $data, 'connect_timeout' => 2, 'timeout' => 6, 'headers' => ['backApiSecret' => opensslEncode($secret)]];
        try {
            $result = $client->request("POST", $url, $options);
            if ($result->getStatusCode() == 200) {
                //写日志到数据库
                $user = Auth::user();
                RequestApiLog::create([
                    'url' => $url,
                    'params' => json_encode($data),
                    'ip' => request()->ip(),
                    'sys_user' => $user->id,
                    'sys_user_name' => $user->name,
                    'role_id' => $user->role_id
                ]);
            }
            $res = json_decode($result->getBody(), true);
            if (!$res['code']) return failReturn($res['msg']??'操作失败');
            else return jsonReturn($res['data'], 1, '操作成功');
        } catch (\Exception $e) {
            \Log::info(["msg" => $e->getMessage()] + ["position" => "请求错误"] + $data);
            return failReturn("服务端错误，请稍后重试");
        }
    }
}

/**
 * 钱转换为汉字表示
 */
if (!function_exists("num_to_rmb")) {
    function num_to_rmb($num)
    {
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        //精确到分后面就不要了，所以只留两个小数位
        $num = round($num, 2);
        //将数字转化为整数
        $num = $num * 100;
        if (strlen($num) > 10) {
            return "金额太大，请检查";
        }
        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                //获取最后一位数字
                $n = substr($num, strlen($num) - 1, 1);
            } else {
                $n = $num % 10;
            }
            //每次将最后一位数字转化为中文
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                $c = $p1 . $p2 . $c;
            } else {
                $c = $p1 . $c;
            }
            $i = $i + 1;
            //去掉数字最后一位了
            $num = $num / 10;
            $num = (int)$num;
            //结束循环
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
            //utf8一个汉字相当3个字符
            $m = substr($c, $j, 6);
            //处理数字中很多0的情况,每次循环去掉一个汉字“零”
            if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left . $right;
                $j = $j - 3;
                $slen = $slen - 3;
            }
            $j = $j + 3;
        }
        //这个是为了去掉类似23.0中最后一个“零”字
        if (substr($c, strlen($c) - 3, 3) == '零') {
            $c = substr($c, 0, strlen($c) - 3);
        }
        //将处理的汉字加上“整”
        if (empty($c)) {
            return "零元整";
        } else {
            return $c . "整";
        }
    }
}

/**
 * 格式化数据
 */
if (!function_exists("formatAll")) {
    function formatAll($arr = [])
    {
        if (!empty($arr)) {
            foreach ($arr as &$v) {
                if (is_numeric($v)) $v = round($v, 3);
                /*else if(is_array($v)){
                    $v = formatAll($v);
                }*/
            }
        }
        return $arr;
    }
}


/**
 * 格式化钱
 */
if (!function_exists("formatMoney")) {
    function formatMoney($money, $decimalNum = 2)
    {
        return sprintf("%.{$decimalNum}f", round((float)$money, $decimalNum));
    }
}

/**
 * 阿里oss上传
 */
if (!function_exists("ossUpload")) {
    function ossUpload($object, $content, $type = "")
    {
        $accessKeyId = "LTAI752rbfc4enCB";
        $accessKeySecret = "4H0xk2HVcoHvOBPUzJ4XntfISL8W8t";
        $endpoint = OSS_END_POINT_URL;
        $bucket = config("env.OSS_BUCKET_NAME");
        if ($type) {
            $object = $type . "/" . $object;
        }
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->putObject($bucket, $object, $content);
        } catch (OssException $e) {
            \Log::info('上传错误', ["msg" => $e->getMessage(), "position" => "OSS上传错误"]);
            return false;
        }
        return config('env.OSS_BUCKET_URL') . $object;
    }
}

/**
 * 计算下一个工作日的时间
 */
if (!function_exists("calcNextTransactionDays")) {
    function calcNextTransactionDays($startDate, $changeDayNum)
    {
        if ($changeDayNum == 0) return $startDate;

        $changeTime = $changeDayNum * 24 * 3600;
        $startTime = strtotime($startDate);
        //误差时间
        $mistakeDay = ceil($changeDayNum / 3) + 30;
        if ($changeDayNum < 0) {
            $endTime = $startTime;
            $startTime = $startTime - $mistakeDay * 3600 * 24;
        } else {
            $endTime = $startTime + $mistakeDay * 3600 * 24;
        }
        $date = [];
        $tmpTime = $startTime;
        for ($i = 0; $i < abs($changeDayNum) + $mistakeDay - 2; $i++) {
            $date[] = date("Y-m-d", $tmpTime);
            $tmpTime += 3600 * 24;
        }
        $holidays = DB::table("s_holiday_maintain")->where("holiday", ">", $date[0])
            ->where("holiday", "<=", $date[count($date) - 1])->get();
        foreach ($holidays as $holiday) {
            $keys = array_keys($date, $holiday->holiday);
            if (isset($keys[0])) unset($date[$keys[0]]);
        }
        $date = array_values($date);

        return $changeDayNum > 0 ? $date[$changeDayNum] : $date[count($date) - 1 - abs($changeDayNum)];
    }
}

/**
 * 计算相隔多少个工作日
 */
if (!function_exists("calcTransactionDays")) {
    function calcTransactionDays($startDate, $endDate)
    {
        if (strtotime($startDate) > strtotime($endDate)) return false;

        $startTime = strtotime(Date("Y-m-d", strtotime($startDate)));
        $endTime = strtotime(Date("Y-m-d", strtotime($endDate)));
        $dates = [];
        while ($startTime <= $endTime) {
            $dates[] = date("Y-m-d", $startTime);
            $startTime += 3600 * 24;
        }

        $holidays = DB::table("s_holiday_maintain")->whereIn("holiday", $dates)->count();
        return count($dates) - $holidays;
    }
}

/**
 * 获取代理商ID
 */
if (!function_exists("getAgentID")) {
    function getAgentID()
    {
        if ($user = Auth::user()) return $user->agent_id;
        else return SystemParam::getAgentIdByHost();
    }
}

if (!function_exists("getTree")) {
    function getTree($list, $id = 0)
    {
        $array = [];
        foreach ($list as $k => $v) {
            if ($v['pid'] == $id) {
                $v['children'] = getTree($list, $v['id']);
                $array[] = $v;
            }
        }
        return $array;
    }
}

//判断是否是移动端访问
if (!function_exists("isMobile")) {
    function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return TRUE;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])) {
            return stristr($_SERVER['HTTP_VIA'], "wap") ? TRUE : FALSE;// 找不到为flase,否则为TRUE
        }
        // 判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'mobile',
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return TRUE;
            }
        }
        if (isset ($_SERVER['HTTP_ACCEPT'])) { // 协议法，因为有可能不准确，放到最后判断
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== FALSE) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === FALSE || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return TRUE;
            }
        }
        return FALSE;
    }
}