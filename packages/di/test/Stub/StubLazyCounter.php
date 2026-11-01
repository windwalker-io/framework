<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

/**
 * Counts constructor calls to verify lazy initialization timing.
 *
 * Used by ContainerLazyTest. Remove together with those tests.
 */
class StubLazyCounter
{
    public static int $created = 0;

    public string $name = 'origin';

    public function __construct()
    {
        self::$created++;
    }

    public function hello(): string
    {
        return 'hello ' . $this->name;
    }
}
