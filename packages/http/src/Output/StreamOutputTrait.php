<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\Output;

use Psr\Http\Message\ResponseInterface;

/**
 * Trait StreamOutputTrait
 */
trait StreamOutputTrait
{
    protected int $maxBufferLength = 8192;

    /**
     * Delay every loop for microseconds.
     *
     * @var int|null
     */
    protected ?int $delay = null;

    /**
     * Send body as response.
     *
     * @param  ResponseInterface  $response  Response object.
     *
     * @return  void
     */
    public function sendBody(ResponseInterface $response): void
    {
        $maxBufferLength = $this->getMaxBufferLength();

        if ($maxBufferLength <= 0) {
            $this->write((string) $response->getBody());
            return;
        }

        $range = $this->getContentRange($response->getHeaderLine('content-range'));

        if ($range === false) {
            $body = $response->getBody();

            if ($body->isSeekable()) {
                $body->rewind();
            }

            while (!$body->eof()) {
                $this->write($body->read($maxBufferLength));

                $this->delay();
            }

            return;
        }

        [$unit, $first, $last, $length] = array_values($range);

        ++$last;

        $body = $response->getBody();
        $body->seek($first);
        $position = $first;

        while (!$body->eof() && $position < $last) {
            // The latest part
            if (($position + $maxBufferLength) > $last) {
                $this->write($body->read($last - $position));

                $this->delay();

                break;
            }

            $this->write($body->read($maxBufferLength));

            $position = $body->tell();

            $this->delay();
        }
    }

    /**
     * Prepare content-length header.
     *
     * @param  ResponseInterface  $response  The response object with headers.
     *
     * @return  ResponseInterface
     */
    public static function prepareContentLength(ResponseInterface $response): ResponseInterface
    {
        if (!$response->hasHeader('content-length')) {
            if ($response->getBody()->getSize() !== null) {
                return $response->withHeader('content-length', (string) $response->getBody()->getSize());
            }
        }

        return $response;
    }

    /**
     * Parse content-range header to an array.
     *
     * @see  http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.16
     *
     * @param  string  $header
     *
     * @return  false|array  An array with [unit, first, last, length] elements;
     */
    protected function getContentRange(string $header): bool|array
    {
        if (preg_match('/(?P<unit>[\w]+)\s+(?P<first>\d+)-(?P<last>\d+)\/(?P<length>\d+|\*)/', $header, $matches)) {
            $return = [];

            $return['unit'] = $matches['unit'];
            $return['first'] = (int) $matches['first'];
            $return['last'] = (int) $matches['last'];
            $return['length'] = is_numeric($matches['length']) ? (int) $matches['length'] : '*';

            return $return;
        }

        return false;
    }

    /**
     * Method to get property MaxBufferLength
     *
     * @return  int
     */
    public function getMaxBufferLength(): int
    {
        return $this->maxBufferLength;
    }

    /**
     * Method to set property maxBufferLength
     *
     * @param  int  $maxBufferLength
     *
     * @return  static  Return self to support chaining.
     */
    public function setMaxBufferLength(int $maxBufferLength): static
    {
        $this->maxBufferLength = $maxBufferLength;

        return $this;
    }

    /**
     * Method to get property Delay
     *
     * @return int|null
     */
    public function getDelay(): ?int
    {
        return $this->delay;
    }

    /**
     * Method to set property delay
     *
     * @param  int  $delay
     *
     * @return  static  Return self to support chaining.
     */
    public function setDelay(int $delay): static
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Delay the output loop.
     *
     * @return  void
     */
    protected function delay(): void
    {
        if ($this->delay === null) {
            return;
        }

        usleep($this->delay);
    }
}
