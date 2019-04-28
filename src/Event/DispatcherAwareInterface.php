<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Event;

/**
 * Interface DispatcherAwareInterface
 */
interface DispatcherAwareInterface
{
    /**
     * getDispatcher
     *
     * @return  DispatcherInterface
     */
    public function getDispatcher();

    /**
     * setDispatcher
     *
     * @param   DispatcherInterface $dispatcher
     *
     * @return  static  Return self to support chaining.
     */
    public function setDispatcher(DispatcherInterface $dispatcher);
}
