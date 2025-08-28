<?php

declare(strict_types=1);

namespace Windwalker\Http\Event\HttpClient;

use Windwalker\Event\BaseEvent;
use Windwalker\Http\HttpClientOptions;
use Windwalker\Http\HttpClient;

/**
 * The BeforeRequestEvent class.
 */
class BeforeRequestEvent extends BaseEvent
{
    public function __construct(
        public HttpClient $httpClient,
        public string $method,
        public string $url,
        public mixed $body,
        public HttpClientOptions $options,
    ) {
        //
    }

    /**
     * @return string
     *
     * @deprecated  Use property instead.
     */
    public function &getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param  string  $method
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     *
     * @deprecated  Use property instead.
     */
    public function &getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param  string  $url
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     *
     * @deprecated  Use property instead.
     */
    public function &getBody(): mixed
    {
        return $this->body;
    }

    /**
     * @param  mixed  $body
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setBody(mixed $body): static
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return HttpClientOptions
     *
     * @deprecated  Use property instead.
     */
    public function getOptions(): HttpClientOptions
    {
        return $this->options;
    }

    /**
     * @param  HttpClientOptions  $options
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setOptions(HttpClientOptions $options): static
    {
        $this->options = $options;

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
}
