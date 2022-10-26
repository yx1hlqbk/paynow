<?php
require "../../vendor/autoload.php";

use Ian\PayNow\PayNow;

try {
    $PayNow = new PayNow();
    $logistic = $PayNow->logistic('', ''); // 設定參數
    $data = [
        "OrderNo"=> "333333",
        "TotalAmount"=>"290",
        "receiver_storeid" => "",
        "receiver_storename" => "",
        "Receiver_Name" => "",
        "Receiver_Phone" => ""
    ];
    $result = $logistic->update('02', $data, '');
    echo '<pre>';
    print_r($result);
} catch (\Throwable $th) {
    echo $th->getMessage();
}