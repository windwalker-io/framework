<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Output;

use Psr\Http\Message\ResponseInterface;

/**
 * OutputInterface
 *
 * @since  3.0
 */
interface OutputInterface
{
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
    public function respond(ResponseInterface $response): void;

    /**
     * Method to send a header to the client.  We wrap header() function with this method for testing reason.
     *
     * @param  string    $string   The header string.
     * @param  bool      $replace  The optional replace parameter indicates whether the header should
     *                             replace a previous similar header, or add a second header of the same type.
     * @param  int|null  $code     Forces the HTTP response code to the specified value. Note that
     *                             this parameter only has an effect if the string is not empty.
     *
     * @return  static  Return self to support chaining.
     *
     * @see     header()
     */
    public function header(string $string, bool $replace = true, int $code = null): static;

    /**
     * Is output stream still available.
     *
     * @return  bool
     */
    public function isWritable(): bool;

    /**
     * Write string to output stream.
     *
     * @param  string  $str
     *
     * @return  int Returns the number of bytes written to the stream.
     */
    public function write(string $str): int;

    /**
     * Close ths output stream.
     */
    public function close(): void;
}
