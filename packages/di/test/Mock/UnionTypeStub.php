<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Mock;

use ArrayIterator;
use NonExistsClass;
use Windwalker\Scalars\ArrayObject;

/**
 * The UnionTypeStub class.
 */
class UnionTypeStub
{
    /**
     * UnionTypeStub constructor.
     */
    public function __construct(public NonExistsClass|ArrayObject|ArrayIterator $iter)
    {
    }
}
