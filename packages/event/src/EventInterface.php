<?php

declare(strict_types=1);

namespace Windwalker\Event;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Class EventInterface
 *
 * @since 2.0
 */
interface EventInterface extends StoppableEventInterface
{
    /**
     * Get the event name.
     *
     * @return  string  The event name.
     *
     * @since   2.0
     */
    public function getName(): string;

    /**
     * Clone a new instance with new name. Use for pass Event to another new progress but keep arguments.
     *
     * ```php
     * $event = $dispatcher->emit(new Event('before.run'));
     *
     * // ...
     *
     * $event2 = $dispatcher->emit($event->mirror('after.run'));
     * ```
     *
     * @template  T of EventInterface
     *
     * @param  string|class-string<T>  $name
     * @param  array   $args
     *
     * @return  T
     */
    public function mirror(string $name, array $args): EventInterface;

    /**
     * Stop the event propagation.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function stopPropagation(): void;

    /**
     * getArguments
     *
     * @return  array
     */
    public function &getArguments(): array;
}
