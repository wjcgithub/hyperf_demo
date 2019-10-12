<?php
declare(strict_types=1);

return [
    'name' => 'xesv5_message_queue_http_callback',
    'version' => '0.1',
    'firefly' => [
        'ttl'=>5,
        'timeout'=>1000,
        'heartbeatTime'=>2000,
        'token'=>'9caf50cb7b334beb508a67fe00285f26'
    ],
    'server' => [
        [
            'host'=>'127.0.0.1',
            'port'=>9206,
            'protocol'=>'http',
            'weight'=>1
        ]
    ],
    'middleware'=>[],
    'php'=>'/usr/local/bin/php'
];