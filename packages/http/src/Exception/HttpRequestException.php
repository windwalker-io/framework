<?php

declare(strict_types=1);

namespace Windwalker\Http\Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use UnexpectedValueException;

/**
 * The HttpRequestException class.
 *
 * @since  3.5.2
 */
class HttpRequestException extends UnexpectedValueException
{
    public protected(set) ?ResponseInterface $response = null;

    public StreamInterface|null $body {
        get => $this->response?->getBody();
    }

    protected \Closure $curlCmdCallback;

    public function withResponse(ResponseInterface $response): static
    {
        $new = new static($this->getMessage(), $this->getCode(), $this);
        $new->response = $response;

        return $new;
    }

    public function setCurlCmdCallback(\Closure $callback): static
    {
        $this->curlCmdCallback = $callback;

        return $this;
    }

    public function getBodyString(): string
    {
        $body = $this->body;

        if ($body === null) {
            return '';
        }

        if ($body->isSeekable()) {
            $body->rewind();
        }

        return $body->getContents();
    }

    public function toCurlCmd(): string
    {
        return ($this->curlCmdCallback)();
    }
}
