<?php

declare(strict_types=1);

namespace Windwalker\Http\Event\HttpClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Windwalker\Event\AbstractEvent;
use Windwalker\Http\HttpClient;

/**
 * The AfterRequestEvent class.
 */
class AfterRequestEvent extends AbstractEvent
{
    protected HttpClient $httpClient;

    protected RequestInterface $request;

    protected ResponseInterface $response;

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @param  RequestInterface  $request
     *
     * @return  static  Return self to support chaining.
     */
    public function setRequest(RequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * @param  HttpClient  $httpClient
     *
     * @return  static  Return self to support chaining.
     */
    public function setHttpClient(HttpClient $httpClient): static
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param  ResponseInterface  $response
     *
     * @return  static  Return self to support chaining.
     */
    public function setResponse(ResponseInterface $response): static
    {
        $this->response = $response;

        return $this;
    }
}
