<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\Event\HttpClient;

use Windwalker\Event\AbstractEvent;
use Windwalker\Http\HttpClient;

/**
 * The BeforeRequestEvent class.
 */
class BeforeRequestEvent extends AbstractEvent
{
    protected string $method;

    protected string $url;

    protected mixed $body;

    protected array $options;

    protected HttpClient $httpClient;

    /**
     * @return string
     */
    public function &getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param  string  $method
     *
     * @return  static  Return self to support chaining.
     */
    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function &getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param  string  $url
     *
     * @return  static  Return self to support chaining.
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function &getBody(): mixed
    {
        return $this->body;
    }

    /**
     * @param  mixed  $body
     *
     * @return  static  Return self to support chaining.
     */
    public function setBody(mixed $body): static
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return array
     */
    public function &getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param  array  $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

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
}
