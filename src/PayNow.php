<?php

namespace Ian\PayNow;

use Ian\PayNow\Exception\PayNowException;

class PayNow
{
    /**
     * 環境
     * 
     * @var int
     */
    public $env = 0;

    /**
     * 初始化
     * 
     * @param int $env
     * 
     * @return void
     */
    public function __construct($env = 0)
    {
        $this->env = in_array($env, [0, 1]) ? $env : 0;
    }

    /**
     * 金流功能
     *
     * @param string $mem_cid
     * @param string $mem_password
     *
     * @return \Ian\PayNow\PayNowCash
     * 
     * @throws \Ian\PayNow\Exception\PayNowException
     */
    public function cash($mem_cid = '', $mem_password = '')
    {
        if (empty($mem_cid) || empty($mem_password)) {
            throw new \PayNowException('填寫商戶資訊');
        }

        return new PayNowCash($mem_cid, $mem_password, $this->env);
    }

    /**
     * 發票功能
     *
     * @param string $mem_cid
     * @param string $mem_password
     *
     * @return \Ian\PayNow\PayNowInvoice
     * 
     * @throws \Ian\PayNow\Exception\PayNowException
     */
    public function invoice($mem_cid = '', $mem_password = '')
    {
        if (empty($mem_cid) || empty($mem_password)) {
            throw new \PayNowException('填寫商戶資訊');
        }

        return new PayNowInvoice($mem_cid, $mem_password, $this->env);
    }
}
