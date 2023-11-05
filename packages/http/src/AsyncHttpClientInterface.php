<?php

declare(strict_types=1);

namespace Windwalker\Http;

use Psr\Http\Message\RequestInterface;
use Windwalker\Promise\PromiseInterface;

/**
 * Interface AsyncHttpClientInterface
 */
interface AsyncHttpClientInterface
{
    /**
     * sendAsyncRequest
     *
     * @param  RequestInterface  $request
     *
     * @return  PromiseInterface|mixed
     */
    public function sendAsyncRequest(RequestInterface $request): mixed;
}
