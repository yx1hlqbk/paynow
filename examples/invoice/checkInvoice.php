<?php
require "../../vendor/autoload.php";

use Ian\PayNow\PayNow;

try {
    $PayNow = new PayNow();
    $invoice = $PayNow->invoice('', ''); // 設定參數

    $data = [
        [
            'orderno' => time() . 'D', // 商家訂單編號
            'buyer_id' => '', // 買方統編，若要開統編請填統一編號 若無則填空
            'buyer_name' => 'ian', // 買方名稱
            'buyer_add' => '', // 買方住址，若填入代表要寄送紙本發票 不寄送紙本發票請填空如最前面為 BRING+地址則會保留地址資訊但不寄送發票Ex: BRING 測試地址
            'buyer_phone' => '', // 買方手機，手機格式09xxxxxxxx，如果格式不屬於這個，放空值
            'buer_email' => '', // 買方email，若無填空
            'CarrierType' => '', // 載具類型 10 Y 若無請填空，悠遊卡:1K0001|通用載具:3J0002|自然人憑證:CQ0001|若為統編發票僅能使用通用載具
            'CarrierID_1' => '', // 載具明碼，若無請填空，悠遊卡:免填 | 通用載具:通用載具號碼(手機條碼) | 自然人憑證:憑證號碼
            'CarrierID_2' => '', // 載具隱碼，若無請填空，悠遊卡:悠遊卡隱碼  | 通用載具:通用載具號碼(手機條碼) | 自然人憑證:憑證號碼
            'LoveCode' => '', // 愛心碼，若無請填空
            'Description' => 'ian test1', // 明細描述
            'Quantity' => '1', // 數量
            'UnitPrice' => '3500', // 單價
            'Amount' => '3500', // 小計
            'Remark' => '', // 備註，若為信用卡消費 請帶信用卡末 4 碼
            'ItemTaxtype' => '1', // 發票明細税 1:應稅 2:零稅率 3:免稅
            'IsPassCustoms' => '', // 是否經海關 1:未經海關出口, 2:經海關出口 零稅率為必填，非零稅率發票請留空
        ]
    ];
    echo $invoice->checkInvoice($data);
} catch (\Throwable $th) {
    echo $th->getMessage();
}