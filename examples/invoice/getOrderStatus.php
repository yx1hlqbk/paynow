<?php
require "../../vendor/autoload.php";

use Ian\PayNow\PayNow;

try {
    $PayNow = new PayNow();
    $invoice = $PayNow->invoice('', ''); // 設定參數
    echo $invoice->getOrderStatus('');
} catch (\Throwable $th) {
    echo $th->getMessage();
}