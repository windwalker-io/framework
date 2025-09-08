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

        $empty = true;

        foreach ($channel as $chan) {
            $empty = $this->enqueue($chan) === null && $empty;
        }

        if ($this->options->stopWhenEmpty && $empty) {
            $this->stop('Nothing to enqueue or return from handlers, exit.');
        }
    }

    public function enqueue(string $channel): mixed
    {
        $handler = $this->channelHandlers[$channel] ?? $this->defaultHandler ?? null;

        if (!$handler) {
            return null;
        }

        if ($this->options->maxRuns > 0) {
            $this->runTimes++;
        }

        $controller = $this->createEnqueuerController($channel);
        $controller->queue = $this->queue;

        $event = $this->emit(
            new BeforeEnqueueEvent(
                controller: $controller,
                enqueuer: $this,
                queue: $this->queue,
            )
        );

        $controller = $event->controller;

        try {
            $result = $controller->run($handler);

            $event = $this->emit(
                new AfterEnqueueEvent(
                    controller: $controller,
                    enqueuer: $this,
                    queue: $this->queue,
                    result: $result,
                )
            );

            return $event->result;
        } catch (\Throwable $e) {
            $this->emit(
                new EnqueueFailureEvent(
                    exception: $e,
                    controller: $controller,
                    enqueuer: $this,
                    queue: $this->queue,
                ),
            );

            return null;
        }
    }

    public function default(callable $defaultHandler): static
    {
        $this->defaultHandler = $defaultHandler(...);

        return $this;
    }

    public function channel(string $channel, callable $handler): void
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
