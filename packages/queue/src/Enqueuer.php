<?php

declare(strict_types=1);

namespace Windwalker\Queue;

use Windwalker\Queue\Enqueuer\EnqueuerController;
use Windwalker\Queue\Event\AfterEnqueueEvent;
use Windwalker\Queue\Event\BeforeEnqueueEvent;
use Windwalker\Queue\Event\EnqueueFailureEvent;

/**
 * The Enqueuer class.
 *
 * @psalm-type Invoker = callable(string $channel, callable $invokable): void
 */
class Enqueuer extends AbstractRunner
{
    public protected(set) ?\Closure $defaultHandler = null;

    public protected(set) array $channelHandlers = [];

    public function getRunnerName(): string
    {
        return 'Enqueuer';
    }

    /**
     * @inheritDoc
     */
    public function next(array|string $channel): void
    {
        $channel = (array) $channel;

        foreach ($channel as $chan) {
            $handler = $this->channelHandlers[$chan] ?? $this->defaultHandler ?? null;

            if (!$handler) {
                continue;
            }

            $controller = $this->createEnqueuerController($chan);

            $event = $this->emit(
                new BeforeEnqueueEvent(
                    controller: $controller,
                    enqueuer: $this,
                    queue: $this->queue,
                )
            );

            $controller = $event->controller;

            try {
                $controller->run($handler);

                $event = $this->emit(
                    new AfterEnqueueEvent(
                        controller: $controller,
                        enqueuer: $this,
                        queue: $this->queue,
                    )
                );
            } catch (\Throwable $e) {
                $this->emit(
                    new EnqueueFailureEvent(
                        exception: $e,
                        controller: $controller,
                        enqueuer: $this,
                        queue: $this->queue,
                    ),
                );
            }
        }
    }

    public function setDefaultHandler(callable $defaultHandler): static
    {
        $this->defaultHandler = $defaultHandler(...);

        return $this;
    }

    public function addChannelHandler(string $channel, callable $handler): void
    {
        $this->channelHandlers[$channel] = $handler;
    }

    public function setChannelHandlers(array $channelHandlers): Enqueuer
    {
        $this->channelHandlers = $channelHandlers;

        return $this;
    }

    protected function getDefaultInvoker(): \Closure
    {
        return fn(EnqueuerController $controller, callable $invokable) => $invokable($controller);
    }

    public function createEnqueuerController(string $channel): EnqueuerController
    {
        if ($this->options->controllerFactory) {
            return ($this->options->controllerFactory)(
                $channel,
                $this->getInvoker(),
                $this->createControllerLogger(),
            );
        }

        return new EnqueuerController($channel, $this->getInvoker(), $this->createControllerLogger());
    }
}
