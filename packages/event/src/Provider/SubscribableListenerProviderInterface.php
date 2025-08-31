<?php

declare(strict_types=1);

namespace Windwalker\Event\Provider;

use Psr\EventDispatcher\ListenerProviderInterface;
use Windwalker\Event\Listener\ListenerPriority;

/**
 * The SubscribableListenerProviderInterface class.
 */
interface SubscribableListenerProviderInterface extends ListenerProviderInterface
{
    /**
     * on
     *
     * @param  string    $event
     * @param  callable  $listener
     * @param  int|null  $priority
     *
     * @return  void
     */
    public function on(
        string $event,
        callable $listener,
        ?int $priority = ListenerPriority::NORMAL
    ): void;

    /**
     * subscribe
     *
     * @param  object    $subscriber
     * @param  int|null  $priority
     *
     * @return  void
     */
    public function subscribe(object $subscriber, ?int $priority = null): void;
}
