<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Event\ResponseEvent;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Output\OutputInterface;
use Windwalker\Http\Output\StreamOutput;

/**
 * The WebAdapter class.
 */
class PhpServer implements ServerInterface
{
    use HttpServerTrait;
    use EventAwareTrait;

    public function listen(string $host = '0.0.0.0', int $port = 80, array $options = []): void
    {
        $this->handle($options['request'] ?? null);
    }

    public function handle(?ServerRequestInterface $request = null): void
    {
        $output = $this->createOutput();

        $this->handleRequest(
            $request ?? $this->getHttpFactory()->createServerRequestFromGlobals(),
            $output
        );
    }

    public function stop(): void
    {
        //
    }

    /**
     * @return \Closure|null
     */
    public function getOutputBuilder(): ?\Closure
    {
        return $this->outputBuilder ??= static fn() => new StreamOutput();
    }
}
