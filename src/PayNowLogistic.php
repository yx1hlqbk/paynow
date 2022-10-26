<?php

namespace Ian\PayNow;

use Ian\PayNow\Provider\HttpProvider;

class PayNowLogistic
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
        0 => 'https://testlogistic.paynow.com.tw',
        1 => 'https://logistic.paynow.com.tw'
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

        $this->http = new HttpProvider($this->api_url[$this->env]);
    }


    /**
     * pass code 產生
     * 
     * @param string $data
     * 
     * @return string
     */
    public function createPassCode($data)
    {
        return strtoupper(sha1($data, false));
    }

    /**
     * TripleDESEncoding 產生
     * 
     * @param string $data
     * 
     * @return string
     */
    public function createTripleDESEncoding($data)
    {
        $addString = 8 - (strlen($data) % 8); // 剛好 8的倍數
        $data = str_pad($data, strlen($data) + $addString, ' '); // 用空白補齊
        return base64_encode(openssl_encrypt(
            $data,
            'DES-EDE3',
            '1234567890' . '70828783' . '123456',
            OPENSSL_RAW_DATA | OPENSSL_NO_PADDING
        ));
    }

    /**
     * 選擇超商
     * 
     * @param string $type => 類型 02:7-11 04:family
     * @param string $return_url => 回傳位置
     * 
     * @return string
     */
    public function selectLogistic($type, $return_url)
    {
        $html = '<form id="form" action="' . $this->api_url[$this->env] . '/Member/Order/Choselogistics" method="post">
                    <input type="hidden" name="user_account" value="' . $this->mem_cid . '" />
                    <input type="hidden" name="apicode" value="' . $this->createPassCode($this->mem_password) . '" />
                    <input type="hidden" name="LogisticsSubType" value="" />
                    <input type="hidden" name="Logistic_serviceID" value="' . $type . '" />
                    <input type="hidden" name="returnUrl" value="' . $return_url . '" />
                </form>
                <script>document.getElementById("form").submit();</script>';

        return $html;
    }

    /**
     * 塞入參數
     * 
     * @param array $data
     * 
     * @return array
     */
    public function setData($data)
    {
        return array_merge($data, [
            'user_account' => $this->mem_cid,
            'apicode' => $this->mem_password
        ]);
    }

    /**
     * 建立物流單
     * 
     * @param array $data
     * 
     * @return array
     */
    public function upload($data)
    {
        $data = $this->setData($data);
        $data['PassCode'] = $this->createPassCode($data['user_account'] . $data['OrderNo'] . $data['TotalAmount'] . $data['apicode']);
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $this->http->request('post', '/api/Orderapi/Add_Order', [
            'JsonOrder' => $this->createTripleDESEncoding($data)
        ]);
    }

    /**
     * 取得出貨單號
     * 
     * @param string $type => 類型 02:7-11 04:family
     * @param array $data
     * 
     * @return array
     */
    public function getLogisticNumber($type, $data)
    {
        $data = $this->setData($data);
        $data['PassCode'] = $this->createPassCode($data['user_account'] . $data['apicode']);
        $data = json_encode($data);
        $suffix = $type == '02' ? '/api/Bulk711Order/ShipBulk711paymentno' : '/api/FamiB2COrder/ShipFamiB2Cpaymentno';
        return $this->http->request('post', $suffix, [
            'JsonOrder' => $this->createTripleDESEncoding($data)
        ]);
    }

    /**
     * 查詢物流單 (PayNow 物流單號)
     * 
     * @param string $LogisticNumber
     * @param array $sno
     * 
     * @return array
     */
    public function getInfoByLogisticNumber($LogisticNumber, $sno = '01')
    {
        return $this->http->request('get', '/api/Orderapi/Get_Order_Info?LogisticNumber=' . $LogisticNumber . '&sno=' . $sno);
    }

    /**
     * 查詢物流單 (商家訂單編號)
     * 
     * @param string $LogisticNumber
     * @param array $sno
     * 
     * @return array
     */
    public function getInfoByOrderNumber($orderno, $sno = '01')
    {
        return $this->http->request('get', '/api/Orderapi/Get_Order_Info_orderno?orderno=' . $orderno . '&user_account=' . $this->mem_cid . '&sno=' . $sno);
    }

    /**
     * 取消物流單 (不會有東西回傳)
     * 
     * @param array $datas
     * @param string $LogisticNumber
     * @param string $sno
     * 
     * @return array
     */
    public function cancel($data, $LogisticNumber, $sno = '1')
    {
        return $this->http->request('delete', '/api/Orderapi/CancelOrder', [
            'LogisticNumber' => $LogisticNumber,
            'sno' => $sno,
            'PassCode' => $this->createPassCode($this->mem_cid . $data['OrderNo'] . $data['TotalAmount'] . $this->mem_password),
        ]);
    }

    /**
     * 列印物流單
     * 
     * @param string $type => 類型 02:7-11 04:family
     * @param string $LogisticNumber
     * 
     * @return array
     */
    public function print($type, $LogisticNumber)
    {
        $suffix = $type == '02' ? '/Member/Order/Print711bulkLabel' : '/Member/Order/PrintFamiB2CLabel';
        $html = '<form id="form" action="' . $this->api_url[$this->env] . $suffix . '" method="post">
                    <input type="hidden" name="LogisticNumbers" value="' . $LogisticNumber . '" />
                </form>
                <script>document.getElementById("form").submit();</script>';

        return $html;
    }

    /**
     * 更新物流訂單內容
     * 
     * @param string $type => 類型 02:7-11 04:family
     * @param array $datas
     * @param string $LogisticNumber
     * @param string $sno
     * 
     * @return array
     */
    public function update($type, $data, $LogisticNumber, $sno = '1')
    {
        $suffix = $type == '02' ? '/api/Bulk711Order/UpdateB2C711Order' : '/api/FamiB2COrder/UpdateFamiB2COrder';
        return $this->http->request('put', $suffix, [
            'UpdateOrder' => json_encode([
                'LogisticNumber' => $LogisticNumber,
                'sno' => $sno,
                'PassCode' => $this->createPassCode($this->mem_cid . $data['OrderNo'] . $data['TotalAmount'] . $this->mem_password),
                'receiver_storeid' => $data['receiver_storeid'],
                'receiver_storename' => $data['receiver_storename'],
                'Receiver_Name' => $data['Receiver_Name'],
                'Receiver_Phone' => $data['Receiver_Phone'],
            ])
        ]);
    }

    /**
     * 重新取得出貨單號
     * 
     * @param array $data
     * 
     * @return array
     */
    public function restart($data)
    {
        $data = $this->setData($data);
        $data['PassCode'] = $this->createPassCode($data['user_account'] . $data['OrderNo'] . $data['TotalAmount'] . $data['apicode']);
        $data = json_encode($data);
        return $this->http->request('post', '/api/Orderapi/ReNewOrder', [
            'JsonOrder' => $this->createTripleDESEncoding($data)
        ]);
    }

    /**
     * 建立退貨物流單
     * 
     * @param array $data
     * 
     * @return array
     */
    public function returnOrder($data)
    {
        $data = $this->setData($data);
        $data['PassCode'] = $this->createPassCode($data['user_account'] . $data['LogisticNumber'] . $data['apicode']);
        $data = json_encode($data);
        return $this->http->request('post', '/api/Orderapi/ReturnPaymentno', [
            'JsonOrder' => $this->createTripleDESEncoding($data)
        ]);
    }
}