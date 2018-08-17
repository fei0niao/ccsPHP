<?php

namespace App\Http\Controllers\Api;

use App\Http\Model\Agent;
use App\Http\Model\Msg;

class Feige
{
    private $SmsConfig = [
        "account" => "",
        "pwd" => "",
        "signId" => 0,
        "templateSmsUrl" => "http://api.feige.ee/SmsService/Template",
        "apiSmsUrl" => "http://api.feige.ee/SmsService/Send",
    ];

    private $cust = '';

    // 3是开合约 4是调整合约金额
    public function __construct($cust = '', $type = '', $config = [])
    {
        if (!$config) {
            $Agent = Agent::find($cust->agent_id);
            $config = [
                'account' => $Agent->sms_account,
                'pwd' => $Agent->sms_pwd,
                'signId' => $Agent->sms_sign_id
            ];
        }
        if ($type == 'createContract') {
            $config['type'] = 3; //开合约
        } elseif ($type == 'updateContract') {
            $config['type'] = 4; //调整余额
        } else {
            return false;
        }
        $this->cust = $cust;
        $this->SmsConfig = array_merge($this->SmsConfig, $config);
    }

    /**
     * 发送模板短信接口
     * @param $phone
     * @param $templateId
     * @param string $content
     * @return bool
     */
    public function sendTemplate($templateId, $content = "1", $phone = '')
    {
        if(!$phone) $phone = $this->cust->getOriginal('cellphone');
        $client = new \GuzzleHttp\Client();
        $data = [
            "Account" => $this->SmsConfig["account"],
            "Pwd" => $this->SmsConfig["pwd"],
            "Content" => $content,
            "Mobile" => $phone,
            "TemplateId" => $templateId,
            "signId" => $this->SmsConfig["signId"],
        ];
        $response = $client->request("post", $this->SmsConfig["templateSmsUrl"],
            ["query" => $data,'connect_timeout' => 10,'timeout' => 6]);
        $result = json_decode($response->getBody(), true);
        $ret = $result && $result["Code"] === 0;
        if (!$ret) {
            \Log::info($data + (array)$result + ["position" => "模板短信发送错误"]);
        }
        $this->recordSms($phone, $content, $ret, $templateId);
        return $ret;
    }

    /**
     * 发送文字短信接口
     * @param $phone
     * @param $content
     * @return bool
     */
    public function send($content, $phone = '')
    {
        if(!$phone) $phone = $this->cust->getOriginal('cellphone');
        $client = new \GuzzleHttp\Client();
        $data = [
            "Account" => $this->SmsConfig["account"],
            "Pwd" => $this->SmsConfig["pwd"],
            "Content" => $content,
            "Mobile" => $phone,
            "signId" => $this->SmsConfig["signId"],
        ];
        $response = $client->request("post", $this->SmsConfig["apiSmsUrl"],
            ["query" => $data,'connect_timeout' => 10,'timeout' => 6]);
        $result = json_decode($response->getBody(), true);
        $ret = $result && $result["Code"] === 0;
        if (!$ret) {
            \Log::info($data + (array)$result + ["position" => "短信发送错误"]);
        }
        $this->recordSms($phone, $content, $ret);
        return $ret;
    }

    /**
     * 记录发送短信
     * @param $phone
     * @param $content
     */
    private function recordSms($phone, $content, $ret, $templateId = null)
    {
        $data = [
            "cust_id"  => $this->cust->id,
            "cellphone" => $phone,
            "msg_type" => $this->SmsConfig['type'],
            "template_id" => $templateId,
            "send_time" => date("Y-m-d H:i:s"),
            "msg_content" => $content,
            "status" => $ret ? 1 : 0,
            "agent_id" => $this->cust->agent_id,
        ];
        return Msg::create($data);
    }
}