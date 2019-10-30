<?php
declare(strict_types=1);


namespace App\Common;


class ResponseCode
{
    /**
     * 执行成功
     */
    const SUCCESS = 200;

    /**
     * 执行失败-不重试
     */
    const FAIL_NO_RETRY = 500;

    /**
     * 执行失败-重试
     */
    const FAIL_RETRY = 501;
}