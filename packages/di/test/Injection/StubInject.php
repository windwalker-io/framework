<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Test\Injection;

use Windwalker\DI\Attributes\Inject;

/**
 * The StubInject class.
 *
 * @since  3.4.4
 */
class StubInject
{
    #[Inject]
    public ?StubService $foo = null;

    #[Inject]
    protected ?StubService $bar = null;

    #[Inject('stub')]
    public StubService $baz;

    #[Inject('stub', true)]
    public StubService $yoo;
}
