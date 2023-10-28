<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Reactor\WebSocket;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface WebSocketRequestInterface
 */
interface WebSocketRequestInterface extends ServerRequestInterface
{
    public function getFd(): int;

    public function getData(): string;

    public function getParsedData(): mixed;

    public function withParsedData(mixed $data): static;
}
