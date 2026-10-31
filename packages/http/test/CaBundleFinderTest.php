<?php

declare(strict_types=1);

namespace Windwalker\Http\Test;

use Composer\CaBundle\CaBundle;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\CaBundleFinder;

class CaBundleFinderTest extends TestCase
{
    private \Closure $originalFinder;

    #[Before]
    public function backupFinder(): void
    {
        $this->originalFinder = CaBundleFinder::getFinder();
        CaBundleFinder::setDefaultPath(null);
    }

    #[After]
    public function restoreFinder(): void
    {
        CaBundleFinder::setFinder($this->originalFinder);
        CaBundleFinder::setDefaultPath(null);
    }

    #[Test]
    public function findReturnsExistingDefaultPathFirst(): void
    {
        CaBundleFinder::setDefaultPath(__FILE__);
        CaBundleFinder::setFinder(static fn() => throw new \LogicException('Finder must not be called.'));

        self::assertSame(__FILE__, CaBundleFinder::find());
        self::assertSame(__FILE__, CaBundleFinder::getDefaultPath());
    }

    #[Test]
    public function findFallsBackToFinderWhenDefaultPathDoesNotExist(): void
    {
        CaBundleFinder::setDefaultPath(__DIR__ . '/not-exists.pem');
        CaBundleFinder::setFinder(static fn() => __FILE__);

        self::assertSame(__FILE__, CaBundleFinder::find());
    }

    #[Test]
    public function findReturnsNullWhenNothingExists(): void
    {
        CaBundleFinder::setFinder(static fn() => __DIR__ . '/not-exists.pem');

        self::assertNull(CaBundleFinder::find());
    }

    #[Test]
    public function defaultFinderResolvesBundledCaBundleFile(): void
    {
        self::assertSame(CaBundle::getBundledCaBundlePath(), ($this->originalFinder)());
        self::assertSame(CaBundle::getBundledCaBundlePath(), CaBundleFinder::find());
    }
}
