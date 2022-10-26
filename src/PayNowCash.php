<?php

namespace Ian\PayNow;

class PayNowCash
{
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
     * @param int $env 1:正式 0:測試
     * 
     * @return void
     */
    public function __construct($mem_cid, $mem_password, $env)
    {
        $this->mem_cid = $mem_cid;
        $this->mem_password = $mem_password;
        $this->env = $env;
    }


    /**
     * pass code 產生
     * 
     * @param string $orderNo => 訂單編號
     * @param string $totalPrice => 金額
     * @param string $TranStatus => 狀態 S:成功 F:失敗
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

        // 虛擬帳號(atm) 1:代表回傳參數給商家 0:不帶入
        if ($data['PayType'] == '03') {
            $content .= '<input type="hidden" name="AtmRespost" value="' . ($data['AtmRespost'] ?? 1) . '">';
        }
        
        // 超商戶款 0:7-11(ibon) | 1: FamiPort(全家) 
        if ($data['PayType'] == '05') {
            $content .= '<input type="hidden" name="CodeType" value="' . $data['CodeType'] . '">';
            $content .= '<input type="hidden" name="DeadLine" value="' . ($data['DeadLine'] ?? 1) . '">';
        }

        // 0:中文 1:英文
        if (isset($data['PayEN'])) {
            $content .= '<input type="hidden" name="PayEN" value="' . $data['PayEN'] . '">';
        }
        
        return '<form id="paynow" method="post" action="' . $this->api_url[$this->env] . '">
                    ' . $content . '
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
        $post = !empty($_POST) ? $_POST : json_decode(file_get_contents("php://input"), true);
        if (!empty($post)) {
            $PayType = $post['PayType'] ?? '';
            if (in_array($PayType, ['01', '02', '09'])) {
                // 信用卡 01 | WebATN 02 | 銀聯 09
                $data = [
                    'WebNo' => $post['WebNo'] ?? '',
                    'BuysafeNo' => $post['BuysafeNo'] ?? '',
                    'PassCode' => $post['PassCode'] ?? '',
                    'OrderNo' => $post['OrderNo'] ?? '',
                    'TranStatus' => $post['TranStatus'] ?? '',
                    'ErrDesc' => $post['ErrDesc'] ?? '',
                    'TotalPrice' => $post['TotalPrice'] ?? '',
                    'Note1' => $post['Note1'] ?? '',
                    'Note2' => $post['Note2'] ?? '',
                    'pan_no4' => $post['pan_no4'] ?? '',
                    'Card_Foreign' => $post['Card_Foreign'] ?? '',
                ];
            } elseif ($PayType == '03') {
                // 虛擬帳號
                $data = [
                    'BuysafeNo' => $post['BuysafeNo'] ?? '',
                    'OrderNo' => $post['OrderNo'] ?? '',
                    'TotalPrice' => $post['TotalPrice'] ?? '',
                    'PassCode' => $post['PassCode'] ?? '',
                    'IdKey' => $post['IdKey'] ?? '',
                    'BankCode' => $post['BankCode'] ?? '',
                    'BranchCode' => $post['BranchCode'] ?? '',
                    'ATMNo' => $post['ATMNo'] ?? '',
                    'NewDate' => $post['NewDate'] ?? '',
                    'DueDate' => $post['DueDate'] ?? '',
                    'Note1' => $post['Note1'] ?? '',
                    'Note2' => $post['Note2'] ?? '',
                    'TranStatus' => $post['TranStatus'] ?? '',
                ];
            } elseif ($PayType == '10') {
                // 超商條碼
                $data = [
                    'BuysafeNo' => $post['BuysafeNo'] ?? '',
                    'OrderNo' => $post['OrderNo'] ?? '',
                    'TotalPrice' => $post['TotalPrice'] ?? '',
                    'PassCode' => $post['PassCode'] ?? '',
                    'BarCode1' => $post['BarCode1'] ?? '',
                    'BarCode2' => $post['BarCode2'] ?? '',
                    'BarCode3' => $post['BarCode3'] ?? '',
                    'NewDate' => $post['NewDate'] ?? '',
                    'DueDate' => $post['DueDate'] ?? '',
                    'TranStatus' => $post['TranStatus'] ?? '',
                ];
            } elseif ($PayType == '05') {
                // ibon/FamiPort
                $data = [
                    'BuysafeNo' => $post['BuysafeNo'] ?? '',
                    'OrderNo' => $post['OrderNo'] ?? '',
                    'TotalPrice' => $post['TotalPrice'] ?? '',
                    'IdKey' => $post['IdKey'] ?? '',
                    'TranStatus' => $post['TranStatus'] ?? '',
                    'ErrDesc' => $post['ErrDesc'] ?? '',
                    'PassCode' => $post['PassCode'] ?? '',
                    'PassCode2' => $post['PassCode2'] ?? '',
                    'Note1' => $post['Note1'] ?? '',
                    'Note2' => $post['Note2'] ?? '',
                ];
            }
    
            $passCode = $this->createPassCode($data['OrderNo'], $data['TotalPrice'], $data['TranStatus']);
            $data['is_verify'] = $passCode == ($data['PassCode'] ?? '');
            return $data;
        } else {
            return [];
        }
    }
}