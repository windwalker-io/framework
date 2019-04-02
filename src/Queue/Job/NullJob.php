<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Queue\Job;

/**
 * The NullJob class.
 *
 * @since  3.2
 */
class NullJob implements JobInterface
{
    /**
     * getName
     *
     * @return  string
     */
    public function getName()
    {
        return 'null';
    }

    public function execute()
    {
    }
}
