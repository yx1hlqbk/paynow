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
     * @param string $TranStatus
     * 
     * @return string
     */
    public function createPassCode($orderNo, $totalPrice, $TranStatus = '')
    {
        return strtoupper(sha1($this->mem_cid . $OrderNo . $totalPrice . $this->mem_password . $TranStatus, false));
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

        if ($data['PayType'] == '03') {
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
     * @return array
     */
    public function callBack()
    {
        $PayType = $_POST['PayType'] ?? '';
        if (in_array($PayType, ['01', '02', '09'])) {
            // 信用卡 01 | WebATN 02 | 銀聯 09
            $data = [
                'WebNo' => $_POST['WebNo'] ?? '',
                'BuysafeNo' => $_POST['BuysafeNo'] ?? '',
                'PassCode' => $_POST['PassCode'] ?? '',
                'OrderNo' => $_POST['OrderNo'] ?? '',
                'TranStatus' => $_POST['TranStatus'] ?? '',
                'ErrDesc' => $_POST['ErrDesc'] ?? '',
                'TotalPrice' => $_POST['TotalPrice'] ?? '',
                'Note1' => $_POST['Note1'] ?? '',
                'Note2' => $_POST['Note2'] ?? '',
                'pan_no4' => $_POST['pan_no4'] ?? '',
                'Card_Foreign' => $_POST['Card_Foreign'] ?? '',
            ];
        } elseif ($PayType == '03') {
            // 虛擬帳號
            $data = [
                'BuysafeNo' => $_POST['BuysafeNo'] ?? '',
                'OrderNo' => $_POST['OrderNo'] ?? '',
                'TotalPrice' => $_POST['TotalPrice'] ?? '',
                'PassCode' => $_POST['PassCode'] ?? '',
                'IdKey' => $_POST['IdKey'] ?? '',
                'BankCode' => $_POST['BankCode'] ?? '',
                'BranchCode' => $_POST['BranchCode'] ?? '',
                'ATMNo' => $_POST['ATMNo'] ?? '',
                'NewDate' => $_POST['NewDate'] ?? '',
                'DueDate' => $_POST['DueDate'] ?? '',
                'Note1' => $_POST['Note1'] ?? '',
                'Note2' => $_POST['Note2'] ?? '',
                'TranStatus' => $_POST['TranStatus'] ?? '',
            ];
        } elseif ($PayType == '10') {
            // 超商條碼
            $data = [
                'BuysafeNo' => $_POST['BuysafeNo'] ?? '',
                'OrderNo' => $_POST['OrderNo'] ?? '',
                'TotalPrice' => $_POST['TotalPrice'] ?? '',
                'PassCode' => $_POST['PassCode'] ?? '',
                'BarCode1' => $_POST['BarCode1'] ?? '',
                'BarCode2' => $_POST['BarCode2'] ?? '',
                'BarCode3' => $_POST['BarCode3'] ?? '',
                'NewDate' => $_POST['NewDate'] ?? '',
                'DueDate' => $_POST['DueDate'] ?? '',
                'TranStatus' => $_POST['TranStatus'] ?? '',
            ];
        } elseif ($PayType == '05') {
            // ibon/FamiPort
            $data = [
                'BuysafeNo' => $_POST['BuysafeNo'] ?? '',
                'OrderNo' => $_POST['OrderNo'] ?? '',
                'TotalPrice' => $_POST['TotalPrice'] ?? '',
                'IdKey' => $_POST['IdKey'] ?? '',
                'TranStatus' => $_POST['TranStatus'] ?? '',
                'ErrDesc' => $_POST['ErrDesc'] ?? '',
                'PassCode' => $_POST['PassCode'] ?? '',
                'PassCode2' => $_POST['PassCode2'] ?? '',
                'Note1' => $_POST['Note1'] ?? '',
                'Note2' => $_POST['Note2'] ?? '',
            ];
        }

        $passCode = $this->createPassCode($data['OrderNo'], $data['TotalPrice'], $data['TranStatus']);
        $data['is_verify'] = $passCode == $data['PassCode'];
        return $data;
    }
}