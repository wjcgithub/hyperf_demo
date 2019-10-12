<?php
/**
 * Created by PhpStorm.
 * User: evolution
 * Date: 19-10-12
 * Time: 下午7:26
 */

namespace App\Exception\Handler;


use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class CustomValidateException extends ValidationExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        /** @var \Hyperf\Validation\ValidationException $throwable */
        $body = $throwable->validator->errors()->first();
        $rest = [
            'code'=>'-1',
            'msg'=>$body
        ];
        return $response->withStatus($throwable->status)->withBody(new SwooleStream(json_encode($rest)));
    }
}