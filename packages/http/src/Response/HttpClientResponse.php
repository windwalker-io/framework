<?php

declare(strict_types=1);

namespace Windwalker\Http\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\HttpClient;

/**
 * The HttpClientResponse class.
 */
class HttpClientResponse extends Response
{
    /**
     * Same details info from connection handler like CURL or socket.
     *
     * @var mixed
     */
    public protected(set) mixed $info = null;

    public protected(set) RequestInterface $request;

    protected \Closure $curlCmdCallback;

    public static function fromHttpClient(
        RequestInterface $request,
        ResponseInterface $response,
        \Closure $curlCmdCallback,
    ): static {
        $res = static::from($response);
        $res->request = $request;
        $res->curlCmdCallback = $curlCmdCallback;

        return $res;
    }

    public function withInfo(mixed $info): static
    {
        $new = clone $this;
        $new->info = $info;

        return $new;
    }

    public function getInfo(): mixed
    {
        return $this->info;
    }

    public function getBodyString(): string
    {
        $body = $this->getBody();

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
