<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 ${ORGANIZATION}.
 * @license    __LICENSE__
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
