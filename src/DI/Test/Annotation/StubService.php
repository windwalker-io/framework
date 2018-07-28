<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\DI\Test\Annotation;

/**
 * The StubService class.
 *
 * @since  __DEPLOY_VERSION__
 */
class StubService
{
    /**
     * Property counter.
     *
     * @var  int
     */
    public static $counter = 0;

    /**
     * StubService constructor.
     */
    public function __construct()
    {
        static::$counter++;
    }

    /**
     * run
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function run()
    {
        return 'OK';
    }

    /**
     * getCounter
     *
     * @return  int
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getCounter()
    {
        return static::$counter;
    }
}
