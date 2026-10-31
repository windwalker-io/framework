<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Paths;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SplPriorityQueue;
use Windwalker\Filesystem\Path;
use Windwalker\Utilities\Iterator\PriorityQueue;
use Windwalker\Utilities\Paths\PathsAwareTrait;

class PathsAwareTraitTest extends TestCase
{
    /**
     * @see  PathsAwareTrait::getPaths
     */
    #[Test]
    public function getPathsLazyCreatesSameQueue(): void
    {
        $subject = $this->createSubject();

        self::assertSame($subject->getPaths(), $subject->getPaths());
        self::assertCount(0, $subject->getPaths());
    }

    /**
     * @see  PathsAwareTrait::setPaths
     */
    #[Test]
    public function setPathsPrioritizesArrayPathsByPositionAndNormalizesThem(): void
    {
        $subject = $this->createSubject();

        self::assertSame($subject, $subject->setPaths(['/a/b/../c', '/d', '/e']));
        self::assertSame(['/a/c', '/d', '/e'], $this->normalizePaths($subject->dumpPaths()));
    }

    /**
     * @see  PathsAwareTrait::setPaths
     */
    #[Test]
    public function setPathsAcceptsStringAndExistingQueues(): void
    {
        $subject = $this->createSubject();
        $subject->setPaths('/single');

        self::assertSame(['/single'], $this->normalizePaths($subject->dumpPaths()));

        $queue = new PriorityQueue(['/q']);
        $subject->setPaths($queue);

        self::assertInstanceOf(PriorityQueue::class, $subject->getPaths());
        self::assertSame(['/q'], $subject->dumpPaths());

        $spl = new SplPriorityQueue();
        $spl->insert('/spl', 1);
        $subject->setPaths($spl);

        self::assertInstanceOf(PriorityQueue::class, $subject->getPaths());
        self::assertSame(['/spl'], $subject->dumpPaths());
    }

    /**
     * @see  PathsAwareTrait::addPath
     */
    #[Test]
    public function addPathInsertsWithPriority(): void
    {
        $subject = $this->createSubject();
        $subject->addPath('/normal', PriorityQueue::NORMAL);
        $subject->addPath(['/high1/./x', '/high2'], PriorityQueue::HIGH);
        $subject->addPath('/default');

        self::assertSame(['/high1/x', '/high2', '/default', '/normal'], $this->normalizePaths($subject->dumpPaths()));
    }

    /**
     * @see  PathsAwareTrait::mergePaths
     */
    #[Test]
    public function mergePathsCombinesQueue(): void
    {
        $subject = $this->createSubject();
        $subject->addPath('/a', 10);

        $other = new SplPriorityQueue();
        $other->insert('/b', 20);

        self::assertSame($subject, $subject->mergePaths($other));
        self::assertSame(['/b', '/a'], $this->normalizePaths($subject->dumpPaths()));
    }

    /**
     * @see  PathsAwareTrait::clearPaths
     */
    #[Test]
    public function clearPathsRemovesAllPaths(): void
    {
        $subject = $this->createSubject();
        $subject->addPath('/a');

        self::assertSame($subject, $subject->clearPaths());
        self::assertSame([], $subject->dumpPaths());
    }

    /**
     * @see  PathsAwareTrait::getClonedPaths
     * @see  PathsAwareTrait::dumpPaths
     */
    #[Test]
    public function clonedAndDumpedPathsDoNotConsumeOriginalQueue(): void
    {
        $subject = $this->createSubject();
        $subject->addPath(['/a', '/b']);

        $clone = $subject->getClonedPaths();

        self::assertNotSame($subject->getPaths(), $clone);

        $subject->dumpPaths();

        self::assertCount(2, $subject->getPaths());
    }

    /**
     * Anonymous host class for exercising the trait; no longer needed if the trait is removed.
     */
    private function createSubject(): object
    {
        return new class () {
            use PathsAwareTrait;
        };
    }

    /**
     * Normalizes directory separators to forward slashes so path assertions stay stable
     * across Windows and Unix-like systems.
     *
     * @param  array<string>  $paths
     *
     * @return  array<string>
     */
    private function normalizePaths(array $paths): array
    {
        return array_map(static fn(string $path) => Path::clean($path, '/'), $paths);
    }
}
