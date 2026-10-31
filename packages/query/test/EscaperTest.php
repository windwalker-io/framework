<?php

declare(strict_types=1);

namespace Windwalker\Query\Test;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use Windwalker\Query\Escaper;

/**
 * The EscaperTest class.
 */
/**
 * The EscaperTest class.
 */
class EscaperTest extends TestCase
{
    #[Test]
    public function tryEscapeUsesCallable(): void
    {
        $escaper = static fn(string|int $value) => addslashes((string) $value);

        self::assertSame("Simon\\'s", Escaper::tryEscape($escaper, "Simon's"));
    }

    #[Test]
    public function tryEscapeUsesObjectEscapeMethod(): void
    {
        $escaper = new class () {
            public function escape(string|int $value): string
            {
                return strtoupper((string) $value);
            }
        };

        self::assertSame('ABC', Escaper::tryEscape($escaper, 'abc'));
    }

    #[Test]
    public function tryEscapeStripsOuterQuotesFromPdoQuote(): void
    {
        $pdo = new PDO('sqlite::memory:');

        self::assertSame("Simon''s", Escaper::tryEscape($pdo, "Simon's"));
    }

    #[Test]
    public function tryQuoteUsesPdoQuote(): void
    {
        $pdo = new PDO('sqlite::memory:');

        self::assertSame("'Simon''s'", Escaper::tryQuote($pdo, "Simon's"));
    }

    #[Test]
    public function tryQuoteWrapsEscapedValueForCallableEscaper(): void
    {
        $escaper = static fn(string|int $value) => addslashes((string) $value);

        self::assertSame("'Simon\\'s'", Escaper::tryQuote($escaper, "Simon's"));
    }

    #[Test]
    #[DataProvider('stripQuoteProvider')]
    public function stripQuoteRemovesFirstAndLastCharacter(string $value, string $expected): void
    {
        self::assertSame($expected, Escaper::stripQuote($value));
    }

    public static function stripQuoteProvider(): array
    {
        return [
            'single quotes' => ["'foo'", 'foo'],
            'double quotes' => ['"foo"', 'foo'],
            'empty quoted' => ["''", ''],
        ];
    }

    #[Test]
    #[DataProvider('stripQuoteIfExistsProvider')]
    public function stripQuoteIfExistsOnlyStripsMatchingQuotes(
        ?string $value,
        string $sign,
        ?string $sign2,
        ?string $expected
    ): void {
        self::assertSame($expected, Escaper::stripQuoteIfExists($value, $sign, $sign2));
    }

    public static function stripQuoteIfExistsProvider(): array
    {
        return [
            'null stays null' => [null, "'", null, null],
            'quoted' => ["'foo'", "'", null, 'foo'],
            'not quoted' => ['foo', "'", null, 'foo'],
            'only start quoted' => ["'foo", "'", null, "'foo"],
            'different signs' => ['[foo]', '[', ']', 'foo'],
        ];
    }

    #[Test]
    public function instanceEscapeAndQuoteDelegateToConnection(): void
    {
        $connection = new class () {
            public function escape(string|int $value): string
            {
                return 'e' . $value;
            }
        };
        $escaper = new Escaper($connection);

        self::assertSame('efoo', $escaper->escape('foo'));
        self::assertSame("'efoo'", $escaper->quote('foo'));
    }

    #[Test]
    public function getConnectionThrowsWhenReferenceIsDestroyed(): void
    {
        $connection = new class () {
            public function escape(string|int $value): string
            {
                return (string) $value;
            }
        };
        $escaper = new Escaper($connection);
        unset($connection);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageIsOrContains('the reference object has been destroyed');

        $escaper->getConnection();
    }

    #[Test]
    public function setConnectionReplacesConnectionWithWeakReference(): void
    {
        $first = new class () {
            public function escape(string|int $value): string
            {
                return 'a';
            }
        };
        $second = new class () {
            public function escape(string|int $value): string
            {
                return 'b';
            }
        };
        $escaper = new Escaper($first);

        self::assertSame($escaper, $escaper->setConnection($second));
        self::assertSame($second, $escaper->getConnection());
        self::assertSame('b', $escaper->escape('x'));
    }

    #[Test]
    public function isMySqlReturnsFalseForGenericEscapers(): void
    {
        self::assertFalse(Escaper::isMySQL(static fn() => ''));
        $pdo = new PDO('sqlite::memory:');

        self::assertFalse(Escaper::isMySQL(new Escaper($pdo)));
    }
}
