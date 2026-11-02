<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Grammar;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Escaper;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\BaseGrammar;
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

        return new $class()->setEscaper(new Escaper(self::$conn ??= new MockEscaper()));
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

    #[Test]
    public function upsertSupportsCustomValuesAndAccumulatedCalls(): void
    {
        $this->skipIfUpsertUnsupported();

        $query = static::createQuery()
            ->upsert('foo')
            ->columns('id', 'title')
            ->values([1, 'A'])
            ->onConflict('id')
            ->onConflictUpdateColumns('title')
            ->onConflictUpdateValues('hits', raw('hits + 1'));

        $sql = static::normalize($query->render());

        self::assertStringContainsString('hits + 1', $sql);
        self::assertStringNotContainsString('`id` = ', $sql);
        self::assertSame(['id'], $query->getUpsert()['keys']);
        self::assertSame(['title', 'hits'], array_keys($query->getUpsert()['update']));
    }

    #[Test]
    public function upsertDoesNotRepeatPlaceholders(): void
    {
        $this->skipIfUpsertUnsupported();

        $sql = static::normalize(
            static::createQuery()
                ->upsert('foo')
                ->set(['id' => 1, 'title' => 'A'])
                ->onConflict('id')
                ->render()
        );

        preg_match_all('/:p\d+/', $sql, $matches);

        self::assertSame($matches[0], array_unique($matches[0]));
    }

    #[Test]
    public function upsertWithoutDataThrows(): void
    {
        $this->skipIfUpsertUnsupported();

        $this->expectException(\InvalidArgumentException::class);

        static::createQuery()->upsert('foo')->onConflict('id')->render();
    }

    private function skipIfUpsertUnsupported(): void
    {
        if (static::grammarClass() === BaseGrammar::class) {
            $this->markTestSkipped('BaseGrammar does not support upsert.');
        }
    }

    /**
     * Normalize whitespaces and rename random value placeholders to :p1, :p2... by first occurrence.
     */
    protected static function normalize(string $sql): string
    {
        $sql = trim(preg_replace('/\s+/', ' ', $sql));
        $map = [];

        return preg_replace_callback(
            '/:wqp__\d+/',
            static function (array $m) use (&$map) {
                return $map[$m[0]] ??= ':p' . (count($map) + 1);
            },
            $sql
        );
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
        $query = static::createQuery()
            ->upsert($table)
            ->set($data)
            ->onConflict($keys)
            ->onConflictUpdateColumns(...$updateFields)
            ->returning($returningIdField);

        $sql = $query->render();

        return [static::normalize($sql), $query];
    }
}
