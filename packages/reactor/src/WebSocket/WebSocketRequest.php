<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\WebSocket;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Reactor\Swoole\WebSocketFrameWrapper;

/**
 * The WebSocketRequest class.
 */
class WebSocketRequest extends ServerRequest implements WebSocketRequestInterface
{
    protected WebSocketFrameInterface $frame;

    public static function createFromSwooleRequest(Request $request, string $data = ''): static
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

        return $instance->withFrame(new WebSocketFrameWrapper($frame))
            ->withParsedBody($request->post);
    }

    public static function createFromSwooleFrame(Frame $frame): static
    {
        return (new static())->withFrame(new WebSocketFrameWrapper($frame));
    }

    public static function createFromFrame(WebSocketFrameInterface $frame): static
    {
        return (new static())->withFrame($frame);
    }

    public function getFd(): int
    {
        return $this->frame->getFd();
    }

    public function getFrame(): WebSocketFrameInterface
    {
        return $this->frame;
    }

    public function withFrame(WebSocketFrameInterface $frame): static
    {
        $new = clone $this;

        $new->frame = $frame;

        return $new;
    }

    // public function withFrame(WebSocketFrame $frame): static
    // {
    //     $new = clone $this;
    //
    //     $new->frame = $frame;
    //     $data = is_array($frame->data)
    //         ? $frame->data
    //         : (array) json_decode(
    //             $frame->data,
    //             true,
    //             512,
    //             JSON_THROW_ON_ERROR
    //         );
    //
    //     if (count($data) === 2) {
    //         [$route, $data] = $data;
    //
    //         $new->uri = (new Uri())->withPath($route);
    //         $new->attributes = (array) $data;
    //     }
    //
    //     return $new;
    // }

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

    public function getData(): string
    {
        return $this->data;
    }
}
