<?php

namespace Ian\PayNow;

use Ian\PayNow\Provider\HttpProvider;

class PayNowInvoice
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
        0 => 'https://testinvoice.paynow.com.tw/PayNowEInvoice.asmx',
        1 => 'https://invoice.paynow.com.tw/PayNowEInvoice.asmx',
    ];

    /**
     * xmlns url
     * 
     * @var array
     */
    public $xmlns = [
        0 => 'https://testinvoice.PayNow.com.tw/',
        1 => 'https://invoice.PayNow.com.tw/'
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
     * 資料合併
     * 
     * @param array $data
     * 
     * @return string
     */
    private function dataMerge($data)
    {
        $csvStr = '';
        foreach ($data as $index => $row) {
            $csvStr .= "'{$row['orderno']},"; // 商家訂單編號
            $csvStr .= "'{$row['buyer_id']},"; // 買方統編，若要開統編請填統一編號 若無則填空
            $csvStr .= "'{$row['buyer_name']},"; // 買方名稱
            $csvStr .= "'{$row['buyer_add']},"; // 買方住址，若填入代表要寄送紙本發票 不寄送紙本發票請填空如最前面為 BRING+地址則會保留地址資訊但不寄送發票Ex: BRING 測試地址
            $csvStr .= "'{$row['buyer_phone']},"; // 買方手機，手機格式09xxxxxxxx，如果格式不屬於這個，放空值
            $csvStr .= "'{$row['buer_email']},"; // 買方email，若無填空
            $csvStr .= "'{$row['CarrierType']},"; // 載具類型，若無請填空，悠遊卡:1K0001|通用載具:3J0002|自然人憑證:CQ0001|若為統編發票僅能使用通用載具
            $csvStr .= "'{$row['CarrierID_1']},"; // 載具明碼，若無請填空，悠遊卡:免填 | 通用載具:通用載具號碼(手機條碼) | 自然人憑證:憑證號碼
            $csvStr .= "'{$row['CarrierID_2']},"; // 載具隱碼，若無請填空，悠遊卡:悠遊卡隱碼  | 通用載具:通用載具號碼(手機條碼) | 自然人憑證:憑證號碼
            $csvStr .= "'{$row['LoveCode']},"; // 愛心碼，若無請填空
            $csvStr .= "'{$row['Description']},"; // 明細描述
            $csvStr .= "'{$row['Quantity']},"; // 數量
            $csvStr .= "'{$row['UnitPrice']},"; // 單價
            $csvStr .= "'{$row['Amount']},"; // 小計
            $csvStr .= "'{$row['Remark']},"; // 備註，若為信用卡消費 請帶信用卡末 4 碼
            $csvStr .= "'{$row['ItemTaxtype']},"; // 發票明細税 1:應稅 2:零稅率 3:免稅
            $csvStr .= "'{$row['IsPassCustoms']}";  // 是否經海關 1:未經海關出口, 2:經海關出口 零稅率為必填，非零稅率發票請留空

            if ($index + 1 != count($orders)) {
                $csvStr .= chr(10);
            }
        }

        return $csvStr;
    }

    /**
     * 上傳發票檢查
     * 
     * @param array $data
     * 
     * @return string
     */
    public function checkInvoice($data)
    {
        $csvStr = $this->dataMerge($data);

        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
            <soap12:Body>
                <Invoice_PatchData_Check xmlns="' . $this->xmlns[$this->env] . '">
                    <mem_cid>' . $this->mem_cid . '</mem_cid>
                    <mem_password>' . $this->mem_password . '</mem_password>
                    <csvStr>' . urlencode(base64_encode($csvStr)) . '</csvStr>
                </Invoice_PatchData_Check>
            </soap12:Body>
        </soap12:Envelope>';

        return $this->http->requestXml('post', $xml);
    }

    /**
     * 上傳發票
     * 
     * @param array $data
     * 
     * @return string
     */
    public function uploadInvoice($data)
    {
        $csvStr = $this->dataMerge($data);

        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
            <soap12:Body>
                <UploadInvoice_Patch xmlns="' . $this->xmlns[$this->env] . '">
                    <mem_cid>' . $this->mem_cid . '</mem_cid>
                    <mem_password>' . $this->mem_password . '</mem_password>
                    <csvStr>' . urlencode(base64_encode($csvStr)) . '</csvStr>
                </UploadInvoice_Patch>
            </soap12:Body>
        </soap12:Envelope>';

        return $this->http->requestXml('post', $xml);
    }

    /**
     * 發票作廢
     * 
     * @param string $invoiceNumber
     * 
     * @return string
     */
    public function cancelInvoice($invoiceNumber)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
            <soap12:Body>
                <CancelInvoice_I xmlns="' . $this->xmlns[$this->env] . '">
                    <mem_cid>' . $this->mem_cid . '</mem_cid>
                    <InvoiceNo>' . $invoiceNumber . '</InvoiceNo>
                </CancelInvoice_I>
            </soap12:Body>
        </soap12:Envelope>';

        return $this->http->requestXml('post', $xml);
    }

    /**
     * 查詢發票開立狀態
     * 
     * @param string $invoiceNumber
     * 
     * @return string
     */
    public function getInvoiceStatus($invoiceNumber)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
            <soap12:Body>
                <Check_invoice xmlns="' . $this->xmlns[$this->env] . '">
                    <mem_cid>' . $this->mem_cid . '</mem_cid>
                    <InvoiceNo>' . $invoiceNumber . '</InvoiceNo>
                </Check_invoice>
            </soap12:Body>
        </soap12:Envelope>';

        return $this->http->requestXml('post', $xml);
    }

    /**
     * 商家自訂編號查詢
     * 
     * @param string $orderNumber
     * 
     * @return string
     */
    public function getOrderStatus($orderNumber)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
            <soap12:Body>
                <Check_invoiceOrder xmlns="' . $this->xmlns[$this->env] . '">
                    <mem_cid>' . $this->mem_cid . '</mem_cid>
                    <orderno>' . $orderNumber . '</orderno>
                </Check_invoiceOrder>
            </soap12:Body>
        </soap12:Envelope>';

        return $this->http->requestXml('post', $xml);
    }

    /**
     * 以發票號碼查詢(回傳狀態,金額,折讓)
     * 
     * @param string $invoiceNumber
     * 
     * @return string
     * @throws
     */
    public function getInvoiceInfo($invoiceNumber)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
            <soap12:Body>
                <Invoice_Info xmlns="' . $this->xmlns[$this->env] . '">
                    <mem_cid>' . $this->mem_cid . '</mem_cid>
                    <InvoiceNo>' . $invoiceNumber . '</InvoiceNo>
                </Invoice_Info>
            </soap12:Body>
        </soap12:Envelope>';

        return $this->http->requestXml('post', $xml);
    }

    /**
     * 以訂單號碼查詢(回傳狀態,金額,折讓)
     * 
     * @param string $orderNumber
     * 
     * @return string
     * @throws
     */
    public function getOrderInfo($orderNumber)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
            <soap12:Body>
                <Invoice_Info xmlns="' . $this->xmlns[$this->env] . '">
                    <mem_cid>' . $this->mem_cid . '</mem_cid>
                    <OrderNo>' . $orderNumber . '</OrderNo>
                </Invoice_Info>
            </soap12:Body>
        </soap12:Envelope>';

        return $this->http->requestXml('post', $xml);
    }

    /**
     * 取得發票連結 以發票號碼查詢
     * 
     * @param string $invoiceNumber
     * 
     * @return string
     * @throws
     */
    public function getInvoiceUrl($invoiceNumber)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
            <soap12:Body>
                <Get_InvoiceURL_I xmlns="' . $this->xmlns[$this->env] . '">
                    <mem_cid>' . $this->mem_cid . '</mem_cid>
                    <InvoiceNo>' . $invoiceNumber . '</InvoiceNo>
                </Get_InvoiceURL_I>
            </soap12:Body>
        </soap12:Envelope>';

        return $this->http->requestXml('post', $xml);
    }

    /**
     * 取得發票連結 以商家自訂編號查詢
     * 
     * @param string $orderNumber
     * 
     * @return string
     * @throws
     */
    public function getOrderUrl($orderNumber)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
            <soap12:Body>
                <Get_InvoiceURL_O xmlns="' . $this->xmlns[$this->env] . '">
                    <mem_cid>' . $this->mem_cid . '</mem_cid>
                    <OrderNo>' . $orderNumber . '</OrderNo>
                </Get_InvoiceURL_O>
            </soap12:Body>
        </soap12:Envelope>';

        return $this->http->requestXml('post', $xml);
    }
}