<?php
require "../../vendor/autoload.php";

use Ian\PayNow\PayNow;

try {
    $PayNow = new PayNow();
    $logistic = $PayNow->logistic('', ''); // 設定參數
    $data = [
        "ShipList" => [
            [
                "LogisticNumber" => 'WQJA0017B22210260003',
                "sno" => '01' // 開頭要補0
            ]
        ],
    ];
    $result = $logistic->getLogisticNumber($data);
    echo '<pre>';
    print_r($result);
} catch (\Throwable $th) {
    echo $th->getMessage();
}