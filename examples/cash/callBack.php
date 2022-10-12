<?php
require "../../vendor/autoload.php";

use Ian\PayNow\PayNow;

try {
    $PayNow = new PayNow();
    $cash = $PayNow->cash('', ''); // 設定參數
    $result = $cash->callBack();
} catch (\Throwable $th) {
    echo $th->getMessage();
}