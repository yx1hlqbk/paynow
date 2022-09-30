<?php

namespace Ian\PayNow;

use Ian\PayNow\Provider\HttpProvider;
use Ian\PayNow\Support\Utils;

class PayNowCash
{
    /**
     * http providers
     * 
     * @var \Ian\PayNow\Provider\HttpProvider
     */
    public $http;

    /**
     * 環境
     * 
     * @var int
     */
    public $env = 0;

    /**
     * api url
     * 
     * @var array
     */
    public $api_url = [
        0 => 'https://test.paynow.com.tw/service/etopm.aspx',
        1 => 'https://www.paynow.com.tw/service/etopm.aspx',
    ];

    /**
     * 商戶帳號
     * 
     * @var string
     */
    public $mem_cid;

    /**
     * 商戶密碼
     * 
     * @var string
     */
    public $mem_password;

    /**
     * 初始化
     * 
     * @param string $mem_cid
     * @param string $mem_password
     * @param int $env
     * 
     * @return void
     */
    public function __construct($mem_cid, $mem_password, $env)
    {
        $this->mem_cid = $mem_cid;
        $this->mem_password = $mem_password;
        $this->env = $env;

        $this->http = new HttpProvider($this->api_url[$this->env]);
    }


    /**
     * pass code 產生
     * 
     * @param string $orderNo
     * @param string $totalPrice
     * 
     * @return string
     */
    public function createPassCode($orderNo, $totalPrice)
    {
        return strtoupper(sha1($this->mem_cid . $OrderNo . $totalPrice . $this->mem_password, false));
    }

    /**
     * 跳轉付款畫面
     * 
     * @param array $data
     * 
     * @return string
     */
    public function send($data)
    {
        $passCode = $this->createPassCode($data['OrderNo'], $data['TotalPrice']);

        $content = '
            <input type="hidden" name="WebNo" value="' . $this->mem_cid . '">
            <input type="hidden" name="PassCode" value="' . $passCode . '">
            <input type="hidden" name="ReceiverName" value="' . $data['ReceiverName'] . '">
            <input type="hidden" name="ReceiverID" value="' . $data['ReceiverID'] . '">
            <input type="hidden" name="ReceiverTel" value="' . $data['ReceiverTel'] . '">
            <input type="hidden" name="ReceiverEmail" value="' . $data['ReceiverEmail'] . '">
            <input type="hidden" name="OrderNo" value="' . $data['OrderNo'] . '">
            <input type="hidden" name="ECPlatform" value="' . $data['ECPlatform'] . '">
            <input type="hidden" name="TotalPrice" value="' . $data['TotalPrice'] . '">
            <input type="hidden" name="OrderInfo" value="' . $data['OrderInfo'] . '">
            <input type="hidden" name="PayType" value="' . $data['PayType'] . '">
            <input type="hidden" name="EPT" value="1">';

        if ($data['type'] == '02') {
            $content .= '<input type="hidden" name="AtmRespost" value="' . $data['AtmRespost'] . '">';
        }
        
        // 超商戶款 0:7-11(ibon) | 1: FamiPort(全家) 
        if ($data['PayType'] == '05') {
            $content .= '<input type="hidden" name="CodeType" value="' . $data['CodeType'] . '">';
        }
        
        return '<form id="paynow" method="post" action="' . $this->api_url[$this->env] . '">
                    ' . $content . '        
                    <input type="hidden" name="EPT" value="1">
                </form>
                <script> document.getElementById("paynow").submit() </script>';
    }

    /**
     * 回傳處理
     * 
     * @param array $data
     * 
     * @return void
     */
    public function callBack()
    {
        $BuysafeNo = $_POST['BuysafeNo'] ?? '';
        $PassCode = $_POST['PassCode'] ?? '';
        $OrderNo = $_POST['OrderNo'] ?? '';
        $TranStatus = $_POST['TranStatus'] ?? '';
        $ErrDesc = $_POST['ErrDesc'] ?? '';
        $TotalPrice = $_POST['TotalPrice'] ?? '';
        $Note1 = $_POST['Note1'] ?? '';
        $Note2 = $_POST['Note2'] ?? '';
        $PayType = $_POST['PayType'] ?? '';
        $pan_no4 = $_POST['pan_no4'] ?? '';
        $Card_Foreign = $_POST['Card_Foreign'] ?? '';
    }
}