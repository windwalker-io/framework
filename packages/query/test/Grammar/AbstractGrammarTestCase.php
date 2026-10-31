<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Grammar;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Escaper;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;

use function Windwalker\raw;

/**
 * Base class for per-platform grammar tests. Subclasses provide the grammar class under test.
 */
abstract class AbstractGrammarTestCase extends TestCase
{
    // Escaper only keeps a weak reference, so the mock connection must outlive the grammar.
    private static ?MockEscaper $conn = null;

    /**
     * @return  class-string<AbstractGrammar>
     */
    abstract protected static function grammarClass(): string;

    protected static function createGrammar(): AbstractGrammar
    {
        $class = static::grammarClass();

        return (new $class())->setEscaper(new Escaper(self::$conn ??= new MockEscaper()));
    }

    protected static function createQuery(): Query
    {
        return static::createGrammar()->createQuery();
    }

    #[Test]
    public function embeddedQuoteDelimitersAreEscaped(): void
    {
        $grammar = static::grammarClass();
        [$open, $close] = $grammar::$nameQuote;

        $name = 'id' . $close . ' DESC -- ';
        $quoted = $open . 'id' . $close . $close . ' DESC -- ' . $close;

        self::assertSame($quoted, $grammar::quoteName($name));
        self::assertSame($open . 'users' . $close . '.' . $quoted, $grammar::quoteName('users.' . $name));
        self::assertSame($open . 'id' . $close . ' AS ' . $quoted, $grammar::quoteName('id AS ' . $name));
        self::assertSame('*', $grammar::quoteName('*'));
        self::assertSame($open . 'users' . $close . '.*', $grammar::quoteName('users.*'));
        self::assertSame('COUNT(*)', $grammar::quoteName(raw('COUNT(*)')));
        self::assertSame($open . 'a.b' . $close, $grammar::quoteName('a.b', true));
    }

    /**
     * Compile an upsert with a fresh query. Returns the whitespace-normalized SQL and the query for bound checks.
     *
     * @return  array{0: string, 1: Query}
     */
    protected static function compileUpsert(
        array $data,
        array $keys,
        array $updateFields,
        ?string $returningIdField = null,
        string $table = 'foo',
    ): array {
        $query = static::createQuery();

        $sql = $query->getGrammar()->compileUpsert($query, $table, $data, $keys, $updateFields, $returningIdField);

        return [trim(preg_replace('/\s+/', ' ', $sql)), $query];
    }
}
