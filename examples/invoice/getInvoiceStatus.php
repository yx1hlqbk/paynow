<?php
require "../../vendor/autoload.php";

use Ian\PayNow\PayNow;

try {
    $PayNow = new PayNow();
    $invoice = $PayNow->getInvoiceStatus('', ''); // 設定參數
} catch (\Throwable $th) {
    echo $th->getMessage();
}