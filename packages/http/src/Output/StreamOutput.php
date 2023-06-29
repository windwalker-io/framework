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
 * The StreamOutput class.
 *
 * @since  3.0
 */
class StreamOutput extends Output
{
    use StreamOutputTrait;

    /**
     * Method to send the application response to the client.  All headers will be sent prior to the main
     * application output data.
     *
     * @param  ResponseInterface  $response  Respond body output.
     *
     * @return  void
     *
     * @since   3.0
     */
    // public function respond(ResponseInterface $response): void
    // {
    //     $response = static::prepareContentLength($response);
    //
    //     parent::respond($response);
    // }

    public function __destruct()
    {
        $this->outputStream->close();
    }
}
