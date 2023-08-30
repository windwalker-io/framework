<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Reactor\WebSocket;

use Windwalker\Http\Request\ServerRequest;

/**
 * The WebSocketRequest class.
 */
class WebSocketRequest extends ServerRequest implements WebSocketRequestInterface
{
    protected WebSocketFrameInterface $frame;

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
