<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Dumper;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Utilities\Dumper\VarDumper;

class VarDumperTest extends TestCase
{
    use BaseAssertionTrait;

    /**
     * @var  callable|null
     */
    private $originalHandler;

    #[Before]
    public function resetHandler(): void
    {
        // Force lazy re-creation of the default handler.
        $this->originalHandler = VarDumper::setHandler(null);
    }

    #[After]
    public function restoreHandler(): void
    {
        VarDumper::setHandler($this->originalHandler);
    }

    /**
     * @see  VarDumper::dump
     */
    #[Test]
    #[DataProvider('scalarProvider')]
    public function dumpRendersScalarsLikePrintR(mixed $value, string $expected): void
    {
        self::assertSame($expected, VarDumper::dump($value));
    }

    public static function scalarProvider(): array
    {
        return [
            'int' => [1, '1'],
            'string' => ['str', 'str'],
            'float' => [1.5, '1.5'],
            'whole float' => [2.0, '2.0'],
            'inf' => [INF, '(INF)'],
            'negative inf' => [-INF, '(-INF)'],
            'nan' => [NAN, '(NAN)'],
            'null' => [null, '(NULL)'],
            'true' => [true, '(TRUE)'],
            'false' => [false, '(FALSE)'],
        ];
    }

    /**
     * @see  VarDumper::dump
     */
    #[Test]
    public function dumpRendersNestedArrays(): void
    {
        $expected = <<<'TXT'
            Array
            (
                [0] => 1
                [1] => Array
                    (
                        [0] => 2
                    )

            )
            TXT;

        self::assertStringSafeEquals($expected, VarDumper::dump([1, [2]]));
    }

    /**
     * @see  VarDumper::dump
     */
    #[Test]
    public function dumpMarksVisibilityOfObjectProperties(): void
    {
        $object = new class () {
            public int $a = 1;

            protected string $b = 'b';

            private string $c = 'c';
        };

        $result = VarDumper::dump($object);

        self::assertStringContainsString('Object', $result);
        self::assertStringContainsString('[a] => 1', $result);
        self::assertStringContainsString('[b:protected] => b', $result);
        self::assertStringContainsString('[c:private] => c', $result);
    }

    /**
     * @see  VarDumper::dump
     */
    #[Test]
    public function dumpCutsOffAtMaxDepth(): void
    {
        $result = VarDumper::dump(['k' => ['a' => ['b' => 1]]], 1);

        self::assertStringContainsString('*MAX LEVEL*', $result);
        self::assertStringNotContainsString('[b]', $result);
    }

    /**
     * @see  VarDumper::dump
     */
    #[Test]
    public function dumpRendersResources(): void
    {
        $fp = fopen('php://memory', 'r');

        try {
            self::assertStringContainsString('Resource stream', VarDumper::dump($fp));
        } finally {
            fclose($fp);
        }
    }

    /**
     * @see  VarDumper::dump
     */
    #[Test]
    public function dumpAddsDateTimeVirtualProperties(): void
    {
        $result = VarDumper::dump(new \DateTimeImmutable('2020-01-02 03:04:05.123456', new \DateTimeZone('UTC')));

        self::assertStringContainsString('DateTimeImmutable @1577934245 Object', $result);
        self::assertStringContainsString('[date:private] => 2020-01-02T03:04:05.123+00:00', $result);
        self::assertStringContainsString('[DST:private] => Off', $result);
        self::assertMatchesRegularExpression('/\[diff:private] => [+-].+ from now/', $result);
    }

    /**
     * @see  VarDumper::setHandler
     */
    #[Test]
    public function setHandlerReplacesHandlerAndReturnsPreviousOne(): void
    {
        $custom = static fn($var, $depth) => "custom:$var:$depth";

        self::assertNull(VarDumper::setHandler($custom));
        self::assertSame('custom:x:3', VarDumper::dump('x', 3));
        self::assertSame($custom, VarDumper::setHandler(null));
    }

    /**
     * @see  VarDumper::isSupported
     */
    #[Test]
    public function isSupportedDetectsSymfonyCloner(): void
    {
        self::assertTrue(VarDumper::isSupported());
    }
}
