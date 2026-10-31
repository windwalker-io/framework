<?php

namespace Windwalker\Utilities\Test\Stub;

use Windwalker\Utilities\Accessible\AccessibleTrait;
use Windwalker\Utilities\Contract\AccessibleInterface;

/**
 * The StubAccessible class.
 */
class StubAccessible implements AccessibleInterface
{
    use AccessibleTrait;

    /**
     * StubAccessible constructor.
     *
     * @param  array  $storage
     */
    public function __construct(array $storage = [])
    {
        $this->storage = $storage;
    }
}
