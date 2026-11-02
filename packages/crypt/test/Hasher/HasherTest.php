<?php

declare(strict_types=1);

namespace Windwalker\Crypt\Test\Hasher;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Crypt\Hasher\Hasher;

class HasherTest extends TestCase
{
    #[Test]
    public function hashesWithConfiguredAlgorithmAndOutputFormat(): void
    {
        self::assertSame(hash('sha256', 'secret'), new Hasher()->hash('secret'));
        self::assertSame(
            hash('sha512', 'secret', true),
            new Hasher('sha512', true)->hash('secret')
        );
    }

    #[Test]
    public function comparesKnownAndUserStrings(): void
    {
        $hasher = new Hasher();

        self::assertTrue($hasher->equals('same', 'same'));
        self::assertFalse($hasher->equals('known', 'user'));
    }

    #[Test]
    public function exposesSupportedAlgorithms(): void
    {
        self::assertContains('sha256', Hasher::algos());
    }
}
