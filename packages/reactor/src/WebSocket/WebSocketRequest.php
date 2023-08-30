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

    protected mixed $parsedData = null;

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
        return $this->frame?->getData() ?? '';
    }

    public function getParsedData(): mixed
    {
        return $this->parsedData;
    }

    public function withParsedData(mixed $data): static
    {
        $new = clone $this;
        $new->parsedData = $data;

        return $new;
    }
}
