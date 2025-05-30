<?php

declare(strict_types=1);

namespace Windwalker\Http\Event\HttpClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Windwalker\Event\BaseEvent;
use Windwalker\Http\HttpClient;

/**
 * The AfterRequestEvent class.
 */
class AfterRequestEvent extends BaseEvent
{
    public function __construct(
        public HttpClient $httpClient,
        public RequestInterface $request,
        public ResponseInterface $response,
    ) {
        //
    }

    /**
     * @return RequestInterface
     *
     * @deprecated  Use property instead.
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @param  RequestInterface  $request
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setRequest(RequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return HttpClient
     *
     * @deprecated  Use property instead.
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * @param  HttpClient  $httpClient
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setHttpClient(HttpClient $httpClient): static
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @return ResponseInterface
     *
     * @deprecated  Use property instead.
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param  ResponseInterface  $response
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setResponse(ResponseInterface $response): static
    {
        $this->response = $response;

        return $this;
    }
}
