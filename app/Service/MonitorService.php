<?php
declare(strict_types=1);

namespace App\Service;


use Hyperf\Logger\LoggerFactory;
use Lib\Framework\BaseService;

class MonitorService extends BaseService
{
    public $test = 111;
    private $server;
    
    public function mm($params)
    {
        
    }
    
    public function coMonitor()
    {
        swoole_timer_tick(2000, function () {
            $croStat = \Swoole\Coroutine::stats();
            $workerid =
                $this->logger->info(
//                "当前worker[{$workerid}]协程情况" . json_encode($croStat)
                    "当前worker[]协程情况" . json_encode($croStat)
                    , [], 'coroutine_info');
            if ($croStat['coroutine_num'] == 1) {
                $coros = \Swoole\Coroutine::listCoroutines();
                foreach ($coros as $cid) {
                    Log::info(
                        "pid:{pid} 协程具体情况" . json_encode(\Swoole\Coroutine::getBackTrace($cid))
                        , ['{pid}' => posix_getpid()], 'coroutine_info');
                }
            }
        });
    }
}