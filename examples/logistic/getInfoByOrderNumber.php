<?php
require "../../vendor/autoload.php";

use Ian\PayNow\PayNow;

try {
    $PayNow = new PayNow();
    $logistic = $PayNow->logistic('', ''); // 設定參數
    $result = $logistic->getInfoByOrderNumber('333333');
    echo '<pre>';
    print_r($result);
} catch (\Throwable $th) {
    echo $th->getMessage();
}