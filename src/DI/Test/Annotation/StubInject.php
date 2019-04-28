<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\DI\Test\Annotation;

use Windwalker\DI\Annotation\Inject;

/**
 * The StubInject class.
 *
 * @since  3.4.4
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
