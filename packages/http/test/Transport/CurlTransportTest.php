<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Transport;

use Windwalker\Http\Transport\AbstractTransport;
use Windwalker\Http\Transport\CurlTransport;

class CurlTransportTest extends AbstractTransportTestCase
{
    protected array $options = [
        'options' => [CURLOPT_SSL_VERIFYPEER => false],
    ];

    protected function createTransport(): AbstractTransport
    {
        return new CurlTransport();
    }
}
