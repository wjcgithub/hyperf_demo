<?php
/**
 * Created by PhpStorm.
 * User: evolution
 * Date: 19-10-11
 * Time: 下午4:47
 */

namespace App\Controller;


use App\Common\BaseController;
use App\Request\ConsumerRequest;
use App\Service\ConsumerService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * @Controller(prefix="/api/v1")
 */
class ConsumerController extends BaseController
{
    /**
     * @Inject()
     * @var ConsumerService
     */
    private $consumerService;
    
    /**
     * @RequestMapping(path="consumer", methods="get,post")
     */
    public function consumer(ConsumerRequest $request)
    {
        $cmd = $this->request->input('cmd', '');
        $config = $this->request->input('config', []);
        $result = $this->consumerService->consumer($cmd, $config);
        return $this->response->success($result);
    }
}
