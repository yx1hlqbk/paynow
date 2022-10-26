<?php
require "../../vendor/autoload.php";

use Ian\PayNow\PayNow;

try {
    $PayNow = new PayNow();
    $logistic = $PayNow->logistic('', ''); // 設定參數
    $result = $logistic->getInfoByLogisticNumber('WQJA0017B22210260003');
    echo '<pre>';
    print_r($result);
} catch (\Throwable $th) {
    echo $th->getMessage();
}