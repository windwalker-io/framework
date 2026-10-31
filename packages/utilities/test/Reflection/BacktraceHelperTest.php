<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Reflection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Reflection\BacktraceHelper;

class BacktraceHelperTest extends TestCase
{
    #[Test]
    public function findCalledReturnsFrameOutsideTheGivenClass(): void
    {
        $subject = new StubBacktraceSubject();

        $info = $subject->outer(fn() => BacktraceHelper::findCalled(StubBacktraceSubject::class));

        self::assertSame(__FILE__, $info['file']);
        self::assertSame(StubBacktraceSubject::class, $info['class']);
        self::assertSame('outer', $info['function']);
    }

    #[Test]
    public function findCallerReturnsCallerOutsideTheGivenClass(): void
    {
        $subject = new StubBacktraceSubject();

        $info = $subject->outer(fn() => BacktraceHelper::findCaller(StubBacktraceSubject::class));

        self::assertSame(__FILE__, $info['file']);
        self::assertSame(self::class, $info['class']);
        self::assertSame(__FUNCTION__, $info['function']);
        self::assertIsInt($info['line']);
    }

    #[Test]
    public function findCalledAndFindCallerReturnNullWhenClassIsNotInStack(): void
    {
        self::assertNull(BacktraceHelper::findCalled(StubBacktraceSubject::class));
        self::assertNull(BacktraceHelper::findCaller(StubBacktraceSubject::class));
    }

    #[Test]
    public function normalizeBacktraceDescribesArgumentsByType(): void
    {
        $frame = BacktraceHelper::normalizeBacktrace([
            'file' => '/root/dir/File.php',
            'line' => 12,
            'class' => 'Foo\\Bar',
            'type' => '->',
            'function' => 'run',
            'args' => [
                [1],
                new \ArrayObject(),
                'short',
                str_repeat('x', 30),
                '中文中文中文中文中文中文中文中文中文中文中文中文中文中文中文中文中文中文中文中文中文',
                null,
                true,
                false,
                5,
            ],
        ]);

        self::assertSame('/root/dir/File.php:12', $frame['file']);
        self::assertSame('/root/dir/File.php', $frame['pathname']);
        self::assertSame(12, $frame['line']);
        self::assertSame(
            'Foo\\Bar->run(Array, ArrayObject, "short", "' . str_repeat('x', 20) . '...", '
            . '"' . str_repeat('中文', 10) . '...", NULL, TRUE, FALSE, 5)',
            $frame['function']
        );
    }

    #[Test]
    public function normalizeBacktraceHandlesFrameWithoutFileClassAndArgs(): void
    {
        $frame = BacktraceHelper::normalizeBacktrace(['function' => 'foo'], '/root');

        self::assertSame('', $frame['file']);
        self::assertSame('foo()', $frame['function']);
        self::assertNull($frame['pathname']);
        self::assertNull($frame['line']);
    }

    #[Test]
    public function normalizeBacktraceReplacesRootInFile(): void
    {
        $frame = BacktraceHelper::normalizeBacktrace(
            ['file' => '/root/dir/File.php', 'line' => 3, 'function' => 'foo'],
            '/root'
        );

        self::assertSame('ROOT/dir/File.php:3', $frame['file']);
        self::assertSame('/root/dir/File.php', $frame['pathname']);
    }

    #[Test]
    public function normalizeBacktracesKeepsEmptyFramesAsNull(): void
    {
        $frames = BacktraceHelper::normalizeBacktraces(
            [
                ['file' => '/root/a.php', 'line' => 1, 'function' => 'a'],
                [],
            ],
            '/root'
        );

        self::assertSame('ROOT/a.php:1', $frames[0]['file']);
        self::assertNull($frames[1]);
    }

    #[Test]
    public function traceAsStringFormatsIndexedLine(): void
    {
        $line = BacktraceHelper::traceAsString(
            7,
            ['file' => '/root/a.php', 'line' => 1, 'function' => 'a', 'args' => [1]],
            '/root'
        );

        self::assertSame('  7. a(1) ROOT/a.php:1', $line);
    }

    #[Test]
    public function replaceRootReplacesLeadingRootOnly(): void
    {
        self::assertSame('ROOT/a/b.php', BacktraceHelper::replaceRoot('/root/a/b.php', '/root'));
        self::assertSame('ROOT/other/a.php', BacktraceHelper::replaceRoot('/other/a.php', '/root'));
    }
}

/**
 * Fixture that nests two method calls so BacktraceHelperTest can trigger findCalled()/findCaller()
 * from inside an object of a known class.
 *
 * Remove it together with BacktraceHelperTest.
 */
class StubBacktraceSubject
{
    public function outer(callable $callback): ?array
    {
        return $this->inner($callback);
    }

    public function inner(callable $callback): ?array
    {
        return $callback();
    }
}
