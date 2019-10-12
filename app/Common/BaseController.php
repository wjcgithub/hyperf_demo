<?php

declare(strict_types=1);


namespace App\Common;

use Hyperf\HttpServer\Contract\RequestInterface;
use Lib\Framework\Http\Response;
use Lib\Validator\Validator;
use Psr\Container\ContainerInterface;

abstract class BaseController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Validator
     */
    protected $validator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(Response::class);
        $this->request = $container->get(RequestInterface::class);
    }
}
