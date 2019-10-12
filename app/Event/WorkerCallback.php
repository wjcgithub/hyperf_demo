<?php
namespace App\Event;

/**
 * Created by PhpStorm.
 * User: evolution
 * Date: 19-10-11
 * Time: 下午4:42
 */
class WorkerCallback
{
    public function mangerStart(\Swoole\Http\Server $server)
    {
        \Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_ALL);
        \Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_CURL);
    }
}