<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Windwalker\Event\EventListenableInterface;

/**
 * Interface ServerInterface
 */
interface ServerInterface extends EventListenableInterface
{
    public function listen(string $host = '0.0.0.0', int $port = 0, array $options = []): void;

    public function stop(): void;
}
