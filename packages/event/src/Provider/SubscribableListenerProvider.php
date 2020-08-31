<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event\Provider;

use Windwalker\Event\EventInterface;
use Windwalker\Event\EventSubscriberInterface;
use Windwalker\Event\Listener\ListenerPriority;
use Windwalker\Event\Listener\ListenersQueue;
use Windwalker\Utilities\Assert\ArgumentsAssert;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\StrNormalise;

/**
 * The SubscribableListenerProvider class.
 */
class SubscribableListenerProvider implements SubscribableListenerProviderInterface
{
    /**
     * @var ListenersQueue[]
     */
    protected $queues = [];

    /**
     * @inheritDoc
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof EventInterface) {
            $eventName = $event->getName();
        } else {
            $eventName = get_class($event);
        }

        $eventName = strtolower($eventName);

        return $this->queues[$eventName] ?? new ListenersQueue();
    }

    /**
     * @inheritDoc
     */
    public function on(
        string $event,
        callable $listener,
        ?int $priority = ListenerPriority::NORMAL
    ): void {
        $event = strtolower($event);

        if (!isset($this->queues[$event])) {
            $this->queues[$event] = new ListenersQueue();
        }

        $this->queues[$event]->add($listener, $priority);
    }

    /**
     * @inheritDoc
     */
    public function subscribe(object $subscriber, ?int $priority = null): void
    {
        if ($subscriber instanceof EventSubscriberInterface) {
            $events = $subscriber->getSubscribedEvents();
        } else {
            $methods = get_class_methods($subscriber);
            $events  = [];

            foreach ($methods as $method) {
                $events[$method] = [static::normalize($method), $priority];
            }
        }

        foreach ($events as $event => $method) {
            // Register: ['eventName' => 'methodName']
            if (static::isCallable($subscriber, $method)) {
                $this->on(
                    $event,
                    static::toCallable($subscriber, $method),
                    $priority
                );
            } elseif (is_array($method) && $method !== []) {
                if (static::isCallable($subscriber, $method[0])) {
                    // Register: ['eventName' => ['methodName or callable', $priority]]
                    $this->on(
                        $event,
                        static::toCallable($subscriber, $method[0]),
                        $method[1] ?? $priority
                    );
                } else {
                    // Register: ['eventName' => [['methodName1 or callable', $priority], ['methodName2']]]
                    foreach ($method as $subMethod) {
                        $this->on(
                            $event,
                            static::toCallable($subscriber, $subMethod[0]),
                            $subMethod[1] ?? $priority
                        );
                    }
                }
            }
        }
    }

    /**
     * normalize
     *
     * @param  string  $methodName
     *
     * @return  string
     */
    private static function normalize(string $methodName): string
    {
        return lcfirst(StrNormalise::toCamelCase($methodName));
    }

    /**
     * Method to get property Listeners
     *
     * @return  ListenersQueue[]
     *
     * @since  __DEPLOY_VERSION__
     */
    public function &getQueues(): array
    {
        return $this->queues;
    }

    /**
     * isCallable
     *
     * @param  object           $subscriber
     * @param  string|callable  $methodName
     *
     * @return  bool
     */
    private static function isCallable(object $subscriber, $methodName): bool
    {
        if (is_callable($methodName)) {
            return true;
        }

        if (is_string($methodName) && is_callable([$subscriber, $methodName])) {
            return true;
        }

        return false;
    }

    private static function toCallable(object $subscriber, $methodName)
    {
        if (is_callable($methodName)) {
            return $methodName;
        }

        if (is_string($methodName) && is_callable([$subscriber, $methodName])) {
            return [$subscriber, $methodName];
        }

        throw new \InvalidArgumentException(
            sprintf(
                'MethodName should be callable, %s got',
                TypeAssert::describeValue($methodName)
            )
        );
    }
}
