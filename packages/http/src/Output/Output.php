<?php

declare(strict_types=1);

namespace Windwalker\Http\Output;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Response\CallbackResponse;
use Windwalker\Http\Helper\HeaderHelper;
use Windwalker\Stream\Stream;

use const Windwalker\Stream\WRITE_ONLY_RESET;

/**
 * Standard output object for PHP SAPI.
 *
 * @since  3.0
 */
class Output implements OutputInterface
{
    /**
     * Property headerSent.
     *
     * @var  callable
     */
    public $headerSent = 'headers_sent';

    protected StreamInterface $outputStream;

    /**
     * Output constructor.
     *
     * @param  StreamInterface|string|null  $outputStream
     */
    public function __construct(StreamInterface|string|null $outputStream = null)
    {
        $this->outputStream = Stream::wrap($outputStream ?: 'php://output', WRITE_ONLY_RESET);
    }

    /**
     * Method to send the application response to the client.  All headers will be sent prior to the main
     * application output data.
     *
     * @param  ResponseInterface  $response  Respond body output.
     *
     * @return void
     *
     * @since   3.0
     */
    public function respond(ResponseInterface $response): void
    {
        if (!$this->headersSent()) {
            $this->sendStatusLine($response);
            $this->sendHeaders($response);
        }

        if ($response instanceof CallbackResponse) {
            $response->respond($this);
            $this->close();
        } else {
            $this->sendBody($response);
        }
    }

    /**
     * Method to send the application response to the client.  All headers will be sent prior to the main
     * application output data.
     *
     * @param  ResponseInterface  $response  Emmit string to respond.
     *
     * @return void
     */
    public function sendBody(ResponseInterface $response): void
    {
        $this->write((string) $response->getBody());

        $this->close();
    }

    public function write(string $str): int
    {
        return $this->outputStream->write($str);
    }

    public function close(): void
    {
        $this->outputStream->close();
    }

    /**
     * Method to send a header to the client.  We wrap header() function with this method for testing reason.
     *
     * @param  string  $string     The header string.
     * @param  bool    $replace    The optional replace parameter indicates whether the header should
     *                             replace a previous similar header, or add a second header of the same type.
     * @param int|null $code       Forces the HTTP response code to the specified value. Note that
     *                             this parameter only has an effect if the string is not empty.
     *
     * @return  static  Return self to support chaining.
     *
     * @see     header()
     */
    public function header(string $string, bool $replace = true, ?int $code = null): static
    {
        header($string, $replace, (int) $code);

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
            $first = true;
            $header = HeaderHelper::normalizeHeaderName($header);

            foreach ($values as $value) {
                $this->header(sprintf('%s: %s', $header, $value), $first);

                $first = false;
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

        $this->header(
            sprintf(
                'HTTP/%s %d%s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $reasonPhrase
            )
        );
    }

    /**
     * checkHeaderSent
     *
     * @return bool
     */
    public function headersSent(): bool
    {
        return ($this->headerSent)();
    }

    /**
     * @return StreamInterface
     */
    public function getOutputStream(): StreamInterface
    {
        return $this->outputStream;
    }

    /**
     * @param  StreamInterface|string|null  $outputStream
     *
     * @return  static  Return self to support chaining.
     */
    public function setOutputStream(StreamInterface|string|null $outputStream): static
    {
        $this->outputStream = $outputStream;

        return $this;
    }

    public function isWritable(): bool
    {
        return $this->outputStream->isWritable();
    }
}
