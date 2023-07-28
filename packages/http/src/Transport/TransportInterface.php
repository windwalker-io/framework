<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Transport;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Response\HttpClientResponse;

/**
 * The TransportInterface class.
 *
 * @since  2.1
 */
interface TransportInterface
{
    /**
     * Send a request to the server and return a Response object with the response.
     *
     * @param  RequestInterface  $request  The request object to store request params.
     * @param  array             $options  Options array.
     *
     * @return  HttpClientResponse
     *
     * @since   2.1
     */
    public function request(RequestInterface $request, array $options = []): HttpClientResponse;

    /**
     * Use stream to download file.
     *
     * @param  RequestInterface        $request  The request object to store request params.
     * @param  string|StreamInterface  $dest     The dest path to store file.
     *
     * @param  array                   $options
     *
     * @return  HttpClientResponse
     * @since   2.1
     */
    public function download(
        RequestInterface $request,
        string|StreamInterface $dest,
        array $options = []
    ): HttpClientResponse;

    /**
     * Method to check if HTTP transport layer available for using
     *
     * @return  bool  True if available else false
     *
     * @since   2.1
     */
    public static function isSupported(): bool;
}
