<?php

declare(strict_types=1);

use Windwalker\Http\Transport\Options\CurlOptions;

include __DIR__ . '/../../../vendor/autoload.php';

$http = new \Windwalker\Http\HttpClient()
    ->withDefaultHeader('User-Agent', 'Windwalker Http Client')
    ->withBaseUri('https://simular.co/');

// $http->download(
//     'https://getsamplefiles.com/download/zip/sample-3.zip',
//     __DIR__ . '/../tmp/sample-3.zip'
// );

$cmd = $http->toCurlCmd(
    'GET',
    '{a}{?b}',
    null,
    new \Windwalker\Http\ClientOptions(
        transport: new CurlOptions(
            verifyPeer: false
        ),
        vars: [
            'a' => 1,
            'b' => 2,
        ],
        headers: [
            'X-Test' => '123',
        ]
    )
);

echo $cmd;
