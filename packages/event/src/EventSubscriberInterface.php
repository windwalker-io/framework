<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event;

/**
 * Interface EventSubscriberInterface
 */
interface EventSubscriberInterface
{
    /**
     * Get the event subscriber settings.
     *
     * Example:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName or callable', $priority]
     *  * ['eventName' => [['methodName1 or callable', $priority, $once = false], ['methodName2']]]
     *
     * @return  mixed
     */
    public function getSubscribedEvents(): array;
}
