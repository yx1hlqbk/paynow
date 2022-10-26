<?php
require "../../vendor/autoload.php";

use Ian\PayNow\PayNow;

try {
    $PayNow = new PayNow();
    $logistic = $PayNow->logistic('', ''); // 設定參數
    $data = [
        "Logistic_service"=>"02",
        "OrderNo"=> "333333",
        "DeliverMode"=>"02",
        "TotalAmount"=>"290",
        "Remark"=>"",
        "Description"=>"BEVY C. 商品",
        "receiver_storeid"=>"148133",
        "receiver_storename"=>"立行門市",
        "return_storeid"=>"",
        "Receiver_Name"=>"收件測",
        "Receiver_Phone"=>"0912345678",
        "Receiver_Email"=>"123@paynow.com.tw",
        "Receiver_address"=>"新北市三重區力行路二段158號160號",
        "Sender_Name"=>"寄件測",
        "Sender_Phone"=>"0227410417",
        "Sender_Email"=>"test@paynow.com.tw",
        "Sender_address"=>"",
        "Deadline"=>1
    ];
    $result = $logistic->upload($data);
    echo '<pre>';
    print_r($result);
} catch (\Throwable $th) {
    echo $th->getMessage();
}