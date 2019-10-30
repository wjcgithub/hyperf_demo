<?php
namespace App\Common;

use App\Service\MonitorService;
use Hyperf\Di\Container;
use Hyperf\Logger\LoggerFactory;
use Swoole\Server as SwooleServer;


class WorkerCallback
{
    public function tt(SwooleServer $server, int $workerId)
    {
        $container = \Hyperf\Utils\ApplicationContext::getContainer();
        $loggerFactory = $container->get(LoggerFactory::class);
        $logger = $loggerFactory->get('log', 'default');
        $logger->info('test');
        \Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_ALL);
        \Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_CURL);
        var_dump(1111);
//        make(MonitorService::class, compact('server'));
    }
}