<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\Stream;

use Swoole\Http\Response;
use Windwalker\Stream\NullStream;

/**
 * The SwooleResponseStream class.
 */
class SwooleResponseStream extends NullStream
{
    public function __construct(protected Response $response)
    {
    }

    public function close()
    {
        $this->response->end();
    }

    public function write($string): int
    {
        $this->response->write((string) $string);

        return strlen($string);
    }
}
