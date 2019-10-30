<?php
declare(strict_types = 1);

namespace App\Common;

use Hyperf\Logger\LoggerFactory;
use Swlib\Http\ContentType;
use Swlib\Saber;
use Swoole\Coroutine;
use XesMq\Kafka\Consumer as KafkaConsumer;

class Consumer
{
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * @var KafkaConsumer
     */
    private $consumer;
    
    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('log', 'default');
    }
    
    /**
     * @param array $topic
     */
    public function initConsumer(array $topic)
    {
        $this->consumer = new KafkaConsumer();
        $this->consumer->setProxy(config('kafka.kafka_proxy'));
        $this->consumer->setInstanceId($topic['instance_id']);
        $this->consumer->setGroupName($topic['group_name']);
        $this->consumer->setCommitTimeout(5);
        $this->consumer->setMaxConsumeTimes($topic['retry_times']);
        $this->consumer->setMaxProcessing(100);
    }
    
    /**
     * @param array $topic
     */
    public function consumer(array $topic)
    {
        try {
            $this->logger->debug("发送消费请求" . \date("Y-m-d H:i:s"));
            $data = $this->consumer->fetch();
            $this->logger->debug("接收到数据" . \date("Y-m-d H:i:s"));
            if (isset($data['status']) && 1 == $data['status']) {
                $this->logger->debug("消息条数为" . count($data['data']));
                if (0 == count($data['data'])) {
                    Coroutine::sleep(1);
                    return;
                }
                //调用业务方回调方法
                try {
                    foreach ($data['data'] as $item) {
                        $this->call($topic, $item);
                    }
                } catch (\Exception $e) {
                    //如果业务逻辑有异常
                    $this->logger->error("业务逻辑有异常: " . $e->getMessage());
                }
            }
            Coroutine::sleep(1);
        } catch (\Exception $e) {
            $this->logger->error("消费请求异常: file: " . $e->getFile() . "-- line: " . $e->getLine() . "-- msg: " . $e->getMessage() . "--trace: " . $e->getMessage());
            Coroutine::sleep(1);
        }
    }
    
    public function call(array $topic, $data)
    {
        try {
            $saber = Saber::create([
                'headers' => [
                    'Accept-Language' => 'en,zh-CN;q=0.9,zh;q=0.8',
                    'Content-Type' => ContentType::JSON,
                    'User-Agent' => null,
                ],
                'retry_time' => 3,
                'retry' => function (Saber\Request $request) {
                    echo "retry...\n";
                }
            ]);
            echo $saber->get('/get');
            
            $response = Curl::post($topic['callback'], $topic['request_timeout'], [
                'msg' => json_encode($data)
            ]);
            $response = json_decode($response->getBody()->__toString(), true);
        } catch (\Exception $e) {
            $this->logger->info('msg handle failed，request api failed: ' . $e->getMessage() . '--topic:' . $data['topic'] . '--callback' . $topic['callback']);
            if (!$topic['timeout_retry']) {
                if ($topic['save_flag']) {
                    $this->saveMessage($topic['resource_id'], $data);
                }
                $this->commit($data);
            }
            return;
        }
        
        if (isset($response['code'])) {
            switch ($response['code']) {
                case ResponseCode::SUCCESS: {
                    $this->logger->info('success', 'callback', [
                        'topic' => $data['topic'],
                    ]);
                    $this->commit($data);
                    break;
                }
                case ResponseCode::FAIL_NO_RETRY: {
                    if ($topic['save_flag']) {
                        $this->saveMessage($topic['resource_id'], $data);
                    }
                    $this->commit($data);
                    break;
                }
                case ResponseCode::FAIL_RETRY: {
                    $this->logger->info('msg handle failed' . "--topic:{$data['topic']}");
                    break;
                }
                default: {
                    if ($topic['save_flag']) {
                        $this->saveMessage($topic['resource_id'], $data);
                    }
                    $this->commit($data);
                }
            }
        } else {
            $this->logger->info('msg handle failed', 'callback', [
                'topic' => $data['topic'],
            ]);
        }
    }
    
    /**
     * @param $resourceId
     * @param array $msg
     */
    protected function saveMessage($resourceId, array $msg)
    {
        //@todo
//        $mysql = MysqlPDO::Factory(Config::get('mysql'));
//
//        $mysql->query("insert into t_message (resource_id, context, create_at, enable) values (?,?,?,?)", [
//            'resource_id' => $resourceId,
//            'context' => json_encode($msg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
//            'create_at' => time(),
//            'enable' => 1
//        ]);
    }
    
    /**
     * @param $data
     */
    protected function commit($data)
    {
        //$topic to commit
        $ttc = [];
        //对offset进行主题分区分组
        $tpo = array_reduce([$data], function ($carry, $item) {
            if (!isset($carry[$item['topic']])) {
                $carry[$item['topic']] = [];
            }
            if (!isset($carry[$item['topic']][$item['partition']])) {
                $carry[$item['topic']][$item['partition']] = [];
            }
            $carry[$item['topic']][$item['partition']][] = $item['offset'];
            return $carry;
        }, []);
        
        //对offset排序
        foreach ($tpo as $t => $po) {
            foreach ($po as $p => $o) {
                sort($o);
                $oCount = count($o);
                $start = $o[0];
                for ($i = 0; $i < count($o); $i++) {
                    $next = $i + 1;
                    if ($next != $oCount && $o[$next] != $o[$i] + 1) {
                        //区间不连续
                        $end = $o[$i];
                        $ttc[] = [
                            'topic' => $t,
                            'partition' => $p,
                            'startOffset' => $start,
                            'endOffset' => $end,
                        ];
                        $start = $o[$next];
                        $i += 2;
                    }
                }
                $end = $o[$oCount - 1];
                $ttc[] = [
                    'topic' => $t,
                    'partition' => $p,
                    'startOffset' => $start,
                    'endOffset' => $end,
                ];
            }
        }
        $this->consumer->commit($ttc);
    }
}