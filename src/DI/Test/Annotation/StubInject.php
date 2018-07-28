<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\DI\Test\Annotation;

use Windwalker\DI\Annotation\Inject;

/**
 * The StubInject class.
 *
 * @since  __DEPLOY_VERSION__
 */
class StubInject
{
    /**
     * @Inject
     *
     * @var StubService
     */
    public $foo;

    /**
     * @Inject
     *
     * @var StubService
     */
    protected $bar;

    /**
     * @Inject(key="stub")
     *
     * @var StubService
     */
    public $baz;

    /**
     * @Inject(key="stub", new=true)
     *
     * @var StubService
     */
    public $yoo;
}
