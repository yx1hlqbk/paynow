# Paynow 立吉富

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

## 說明

目前有提供電子發票/金流與物流功能，版本資訊如下：

- 發票 v1.5
- 金流 v1.7.0.4
<!-- - 物流 -->

## 安裝

```bash
composer require yx1hlqbk/paynow
```

## 環境要求

\>= PHP 7.4

## 範例

```php
require "./vendor/autoload.php";

use Ian\PayNow\PayNow;

try {
    $PayNow = new PayNow();
    $cash = $PayNow->invoice('', ''); // 使用發票功能

    // ....
} catch (\Throwable $th) {
    echo $th->getMessage();
}
```
