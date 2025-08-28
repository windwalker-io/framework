<?php

declare(strict_types=1);

namespace Windwalker\Http\Transport;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\Transport\Options\CurlOptions;

/**
 * Interface CurlTransportInterface
 */
interface CurlTransportInterface extends TransportInterface
{
    /**
     * @param  string                  $content
     * @param  array                   $info
     * @param  ResponseInterface|null  $response
     *
     * @return  ResponseInterface
     *
     * @psalm-template R
     * @psalm-param R  $response
     * @psalm-return R
     */
    public function contentToResponse(
        string $content,
        array $info,
        ?ResponseInterface $response = null
    ): ResponseInterface;

    /**
     * @param  RequestInterface   $request
     * @param  array|CurlOptions  $options
     *
     * @return  \CurlHandle|false
     */
    public function createHandle(RequestInterface $request, array|CurlOptions $options): \CurlHandle|false;
}
