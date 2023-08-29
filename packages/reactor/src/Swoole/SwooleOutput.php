<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response as SwooleResponse;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Http\Output\OutputInterface;
use Windwalker\Http\Output\StreamOutputTrait;

/**
 * The SwooleOutput class.
 */
class SwooleOutput implements OutputInterface
{
    use StreamOutputTrait;

    public function __construct(protected SwooleResponse $response)
    {
        $this->setDelay(1);
    }

    public function respond(ResponseInterface $response): void
    {
        $this->sendStatusLine($response);
        $this->sendHeaders($response);

        $this->sendBody($response);
    }

    public function header(string $string, bool $replace = true, int $code = null): static
    {
        [$name, $value] = explode(':', $string, 2) + ['', ''];

        $this->response->header($name, trim($value));

        return $this;
    }

    /**
     * Send all response headers.
     *
     * @param  ResponseInterface  $response  Response object to contain headers.
     *
     * @return  static  Instance of $this to allow chaining.
     */
    public function sendHeaders(ResponseInterface $response): static
    {
        foreach ($response->getHeaders() as $header => $values) {
            $header = HeaderHelper::normalizeHeaderName($header);

            foreach ($values as $value) {
                $this->response->header($header, $value);
            }
        }

        return $this;
    }

    /**
     * Send HTTP status line.
     *
     * @param  ResponseInterface  $response  Response object to contain status code and protocol version.
     *
     * @return  void
     */
    public function sendStatusLine(ResponseInterface $response): void
    {
        $reasonPhrase = $response->getReasonPhrase();

        $reasonPhrase = ($reasonPhrase ? ' ' . $reasonPhrase : '');

        $this->response->status($response->getStatusCode(), $reasonPhrase);
    }

    public function write(string $str): int
    {
        if ($str !== '') {
            $this->response->write($str);
        }

        return strlen($str);
    }

    public function close(): void
    {
        $this->response->end();
    }

    public function isWritable(): bool
    {
        return $this->response->isWritable();
    }
}
