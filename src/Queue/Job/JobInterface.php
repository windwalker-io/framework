<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Queue\Job;

/**
 * The AbstractJob class.
 *
 * @since  3.2
 */
interface JobInterface
{
    /**
     * getName
     *
     * @return  string
     */
    public function getName();

    public function execute();
}
