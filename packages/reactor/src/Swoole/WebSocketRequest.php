<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Uri\PsrUri;
use Windwalker\Uri\Uri;

/**
 * The WebSocketRequest class.
 */
class WebSocketRequest extends ServerRequest
{
    protected Frame $frame;

    public static function createFromSwooleRequest(Request $request, mixed $data = []): static
    {
        $frame = new Frame();
        $frame->fd = $request->fd;
        $frame->data = $data;

        $instance = new static(
            (array) $request->server,
            [],
            null,
            null,
            'php://input',
            (array) $request->header,
            (array) $request->cookie,
            (array) $request->get
        );

        return $instance->withFrame($frame)
            ->withParsedBody($request->post);
    }

    public function getFd(): int
    {
        return $this->frame->fd;
    }

    public function getFrame(): Frame
    {
        return $this->frame;
    }

    public function withFrame(Frame $frame): static
    {
        $new = clone $this;

        $new->frame = $frame;
        $data = is_array($frame->data)
            ? $frame->data
            : (array) json_decode(
                $frame->data,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

        if (count($data) === 2) {
            [$route, $data] = $data;

            $new->uri = (new Uri())->withPath($route);
            $new->attributes = (array) $data;
        }

        return $new;
    }

    public function get(string $name): mixed
    {
        return $this->getQueryParams()[$name] ?? null;
    }

    public function withAttributes(array $attributes): static
    {
        $new = clone $this;

        $new->attributes = $attributes;

        return $new;
    }
}
