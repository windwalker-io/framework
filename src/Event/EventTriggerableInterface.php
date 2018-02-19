<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Event;

/**
 * The EventTriggerableInterface interface.
 *
 * @since  2.1.1
 */
interface EventTriggerableInterface
{
    /**
     * Trigger an event.
     *
     * @param   EventInterface|string $event The event object or name.
     * @param   array                 $args  The arguments to set in event.
     *
     * @return  EventInterface  The event after being passed through all listeners.
     *
     * @since   2.0
     */
    public function triggerEvent($event, $args = []);
}
