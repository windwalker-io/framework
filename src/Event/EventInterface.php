<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Event;

/**
 * Class EventInterface
 *
 * @since 2.0
 */
interface EventInterface
{
    /**
     * Get the event name.
     *
     * @return  string  The event name.
     *
     * @since   2.0
     */
    public function getName();

    /**
     * Tell if the event propagation is stopped.
     *
     * @return  boolean  True if stopped, false otherwise.
     *
     * @since   2.0
     */
    public function isStopped();
}
