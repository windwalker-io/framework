<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Proxy;

use function Windwalker\tap;

/**
 * The CachedCallable class.
 */
class CachedCallable extends DisposableCallable
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @inheritDoc
     */
    public function __invoke(...$args): mixed
    {
        if ($this->called) {
            return $this->value;
        }

        $value = parent::__invoke(...$args);

        $this->value = $value;
        $this->called = true;

        return $value;
    }
}
