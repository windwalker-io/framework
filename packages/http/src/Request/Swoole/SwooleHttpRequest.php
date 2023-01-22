<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\Request\Swoole;

use Swoole\Http\Request;
use Windwalker\Http\Factory\ServerRequestFactory;
use Windwalker\Http\Request\ServerRequest;

/**
 * The SwooleHttpRequest class.
 */
class SwooleHttpRequest extends ServerRequest
{
    public int $fd = 0;

    public static function fromSwooleRequest(Request $request)
    {
        $req = ServerRequestFactory::createFromGlobals();
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->fd;
    }

    /**
     * @param  int  $fd
     *
     * @return  static  Return self to support chaining.
     */
    public function withFd(int $fd): static
    {
        $new = clone $this;
        $new->fd = $fd;

        return $new;
    }
}
