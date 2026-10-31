<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Clause;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Clause\Clause;

use function Windwalker\Query\clause;

/**
 * The ClauseTest class.
 */
class ClauseTest extends TestCase
{
    /**
     * @param  string  $name
     * @param  array   $elements
     * @param  string  $glue
     * @param  string  $expected
     *
     * @see          Clause::__toString
     *
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('basicUsageProvider')]
    #[Test]
    public function basicUsageRendersElementsWithGlue(string $name, array $elements, string $glue, string $expected): void
    {
        self::assertEquals(
            $expected,
            (string) new Clause($name, $elements, $glue)
        );
    }

    public static function basicUsageProvider(): array
    {
        return [
            [
                'WHERE',
                ['foo > 0'],
                ' AND ',
                'WHERE foo > 0',
            ],
            [
                'WHERE',
                ['foo > 0', "bar = '123'"],
                ' AND ',
                'WHERE foo > 0 AND bar = \'123\'',
            ],
            [
                'IN()',
                [1, 2, 3],
                ', ',
                'IN(1, 2, 3)',
            ],
            [
                '()',
                ['a = b', 'c = d'],
                ' OR ',
                '(a = b OR c = d)',
            ],
        ];
    }

    #[Test]
    public function nestedClausesRenderRecursively(): void
    {
        $clause = new Clause('WHERE');

        $clause->append(new Clause('', ['foo', '=', "'bar'"]));
        $clause->append(new Clause('OR', ['foo', '<', 5]));
        $clause->append(
            new Clause(
                'AND ()',
                [
                    new Clause('', ['flower', '=', "'sakura'"]),
                    new Clause('OR', ['flower', 'IS', 'NULL']),
                ]
            )
        );

        self::assertEquals(
            'WHERE foo = \'bar\' OR foo < 5 AND (flower = \'sakura\' OR flower IS NULL)',
            (string) $clause
        );
    }

    /**
     * @see  Clause::setGlue
     */
    #[Test]
    public function setGlueChangesGlue(): void
    {
        $clause = new Clause('IN()', [], '');
        $clause->setGlue(', ');

        self::assertEquals(', ', $clause->getGlue());
    }

    /**
     * @see  Clause::setName
     */
    #[Test]
    public function setNameChangesName(): void
    {
        $clause = new Clause('');
        $clause->setName('HELLO');

        self::assertEquals('HELLO', $clause->getName());
    }

    /**
     * @see  Clause::getElements
     */
    #[Test]
    public function setElementsReplacesElements(): void
    {
        $clause = new Clause('IN()', [], '');
        $clause->setElements([1, 2, 3]);

        self::assertEquals([1, 2, 3], $clause->getElements());
    }

    /**
     * @see  Clause::append
     */
    #[Test]
    public function appendAddsElements(): void
    {
        $clause = new Clause('IN()', [], '');
        $clause->append(1);
        $clause->append(2);
        $clause->append(3);

        self::assertEquals([1, 2, 3], $clause->getElements());
    }

    /**
     * @see  Clause::__clone
     */
    #[Test]
    public function cloneDeepCopiesChildClauses(): void
    {
        $clause = new Clause('IN()', [new Clause()], '');
        $clause2 = clone $clause;

        self::assertNotSame(
            $clause->getElements()[0],
            $clause2->getElements()[0]
        );
    }

    #[Test]
    public function magicPropertiesExposeClauseState(): void
    {
        $clause = clause('hello', [1, 2, 3]);

        self::assertEquals('hello', $clause->name);
        self::assertEquals([1, 2, 3], $clause->elements);
        self::assertEquals(' ', $clause->glue);

        self::assertTrue(isset($clause->elements));
        self::assertCount(3, $clause);
    }
}
