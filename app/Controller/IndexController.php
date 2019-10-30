<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Controller;

use Hyperf\Logger\LoggerFactory;
use Swlib\Http\ContentType;
use Swlib\Saber;
use Swlib\SaberGM;
use Swoole\Coroutine;

class IndexController extends AbstractController
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('log', 'default');
    }
    
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();
        
        Coroutine::create(function(){
            echo "aaa";
            $this->logger->info('111111111111111111111111111');
            echo "bbb";
        });
        
        echo "ccc";
    
        Coroutine::create(function(){
            $this->logger->info('222222222222222222222222222');
        });
    
        Coroutine::create(function(){
            $this->logger->info('3333333333333333333');
        });
    
        Coroutine::create(function (){
            $saber = Saber::create([
                'headers' => [
                    'Accept-Language' => 'en,zh-CN;q=0.9,zh;q=0.8',
                    'Content-Type' => ContentType::JSON,
                    'User-Agent' => null,
                ],
                'exception_report'=>0,
                'timeout'=>1,
                'retry_time' => 3,
                'retry' => function (Saber\Request $request) {
                    $this->logger->error("请求失败，　重试中", ['a'=>'a', 'b'=>'b', 'extra'=>['c'=>'c']]);
                }
            ]);
            $response = $saber->get('http://localhost:9505');
            echo $response->getBody();
            echo $response->getStatusCode();
        });
        
        echo "cou======";

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }
    
    public function test()
    {
        return "okok";
    }
}
