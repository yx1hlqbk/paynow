<?php
require "../../vendor/autoload.php";

use Ian\PayNow\PayNow;

try {
    $PayNow = new PayNow();
    $logistic = $PayNow->logistic('', ''); // 設定參數
    echo $logistic->print('02', 'WQJA0017B22210260003_1');
} catch (\Throwable $th) {
    echo $th->getMessage();
}