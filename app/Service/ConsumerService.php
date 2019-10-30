<?php
declare(strict_types=1);

namespace App\Service;


use Hyperf\Utils\Coroutine;
use Lib\Framework\BaseService;
use XesMq\Kafka\Consumer;

class ConsumerService extends BaseService
{
    //topic到协程的映射['mail'=>[1,2,3], 'sms'=>[4,5,6]]
    private $topicTocoroutineMap = [];
    //标记某个协程运行的状态，1:运行，　0:停止，　停止并移除key  ['cid'=>1, 'cid'=>1], 只有1存在
    private $coroutineRunningFlagArr = [];
    
    public function consumer(string  $cmd, array $params) {
        $result = [];
        switch ($cmd) {
            case 'start':
                $topic = $params['topic'] ?? null;
                if (empty($topic)) {
                    return [
                        'code' => '404',
                        'msg' => '请求参数不存在'
                    ];
                }
                
                $this->exec($topic);

//            $process = new Process(function (Process $worker) use ($topic) {
//                $php = \Pool\Config::get('app.php');
//                $worker->exec($php ?? '/usr/bin/php', [__DIR__ . '/xesv5Consumer.php', json_encode($topic)]);
//            }, false, 0, false);
//
//            $pid = $process->start();
//                $pidList[$topic['instance_id']] = $coid;
//
//                $response->end(json_encode([
//                    'code' => '200',
//                    'pid' => $coid
//                ]));
//                return;
//            }
//            case 'stop': {
//                $topic = $params['topic'] ?? null;
//                if (empty($topic)) {
//                    $response->end(json_encode([
//                        'code' => '404',
//                        'msg' => '请求参数不存在'
//                    ]));
//                    return;
//                }
//                if (!isset($pidList[$topic['instance_id']])) {
//                    $response->end(json_encode([
//                        'code' => '404',
//                        'msg' => '进程不存在'
//                    ]));
//                    return;
//                }
//                Process::kill($pidList[$topic['instance_id']], 15);
//                return;
//            }
            default: {
                return [
                    'code' => '404',
                    'msg' => '错误的命令'
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * 执行具体消费方式
     *
     * @param array $topic
     */
    public function exec(array $topic)
    {
        Coroutine::create(function () {
            $consumer = new \App\Common\Consumer();
            $consumer->initConsumer($topic);
            $this->coroutineRunningFlagArr[Coroutine::id()] = 1;
            $this->topicTocoroutineMap[$topic['topic']] = Coroutine::id();
            while ($this->coroutineRunningFlagArr[Coroutine::id()]) {
                $consumer->consumer($topic);
            }
        });
    }
}