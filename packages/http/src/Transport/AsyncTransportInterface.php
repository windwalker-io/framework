<?php

declare(strict_types=1);

namespace Windwalker\Http\Transport;

use Psr\Http\Message\RequestInterface;
use Windwalker\Http\Transport\Options\TransportOptions;
use Windwalker\Promise\PromiseInterface;

/**
 * Interface AsyncTransportInterface
 */
interface AsyncTransportInterface
{
    /**
     * sendRequest
     *
     * @param  RequestInterface        $request
     *
     * @param  array|TransportOptions  $options
     *
     * @return  mixed|PromiseInterface
     */
    public function sendRequest(RequestInterface $request, array|TransportOptions $options = []): mixed;
}
