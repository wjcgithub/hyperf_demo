<?php
declare(strict_types=1);

namespace App\Controller;


use App\Service\MonitorService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Common\BaseController;

/**
 * @Controller(prefix="/api/v1")
 */
class MonitorController extends BaseController
{
    /**
     * @Inject()
     * @var MonitorService
     */
    private $monitorService;
    
    /**
     * @RequestMapping(path="monitor", methods="get,post")
     */
    public function consumer()
    {
        
        var_dump($this->request->post("q"));
        return;
        
        
        $this->request->dd = 111;
        $this->monitorService->test = "consumer";
        echo "======monitor->test on consumer:".$this->monitorService->test."\r\n";
        
        
        
        var_dump(get_class($this->request));
        var_dump($this->request->post("cmd"));
        $this->monitorService->mm($this->request->all('a'));
//        $result = $this->monitorService->get();
//        return $this->response->success($result);
    }
    
    /**
     * @RequestMapping(path="callback", methods="get,post")
     */
    public function callback()
    {
        
        var_dump($this->request->post("q"));
        return;
        
        echo "======monitor->request dd params:".($this->request->dd ?? 'none')."\r\n";
        echo "======monitor->test on callback:".$this->monitorService->test."\r\n";
        
        
        
        var_dump($this->request->all());

//        $result = $this->request->post('payload', '');
//        return $this->response->success($result);
    }
}