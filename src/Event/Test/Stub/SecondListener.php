<?php declare(strict_types=1);
/**
 * @copyright  Copyright (C) 2019 LYRASOFT Source Matters.
 * @license    LGPL-2.0-or-later.txt
 */

namespace Windwalker\Event\Test\Stub;

use Windwalker\Event\Event;

/**
 * A listener used to test the triggerEvent method in the dispatcher.
 * It will be added in second position.
 *
 * @since  2.0
 */
class SecondListener
{
    /**
     * Listen to onSomething.
     *
     * @param   Event $event The event.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function onSomething(Event $event)
    {
        $listeners = $event->getArgument('listeners');

        $listeners[] = 'second';

        $event->setArgument('listeners', $listeners);
    }
}
