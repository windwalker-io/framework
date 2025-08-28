<?php

declare(strict_types=1);

namespace Windwalker\Http\Transport;

use Psr\Http\Message\RequestInterface;
use Windwalker\Http\CaBundleFinder;
use Windwalker\Http\Response\HttpClientResponse;
use Windwalker\Http\Transport\Options\TransportOptions;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The AbstractTransport class.
 *
 * @since  2.1
 */
abstract class AbstractTransport implements TransportInterface
{
    use OptionAccessTrait;

    /**
     * Send a request to the server and return a Response object with the response.
     *
     * @param  RequestInterface        $request  The request object to send.
     * @param  array|TransportOptions  $options
     *
     * @return  HttpClientResponse
     *
     * @since   2.1
     */
    public function request(RequestInterface $request, array|TransportOptions $options = []): HttpClientResponse
    {
        $uri = (string) $request->getUri()
            ?->withPath('')
            ?->withQuery('')
            ?->withFragment('');

        $uri .= $request->getRequestTarget();

        $request = $request->withRequestTarget($uri);

        return $this->doRequest($request, $options);
    }

    /**
     * Send a request to the server and return a Response object with the response.
     *
     * @param  RequestInterface        $request  The request object to store request params.
     * @param  array|TransportOptions  $options
     *
     * @return  HttpClientResponse
     *
     * @since   2.1
     */
    abstract protected function doRequest(
        RequestInterface $request,
        array|TransportOptions $options = []
    ): HttpClientResponse;

    /**
     * @return  ?string
     */
    protected function findCAPathOrFile(): ?string
    {
        return CaBundleFinder::find();
    }
}
