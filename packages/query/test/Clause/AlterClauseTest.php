<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Clause;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;

class AlterClauseTest extends TestCase
{
    #[Test]
    public function renderBuildsAlterTableAddIndex(): void
    {
        $alter = self::createQuery()->alter('TABLE', 'foo');
        $alter->addIndex('idx_sakura', ['id', 'sakura']);

        self::assertEquals("ALTER TABLE \"foo\"\nADD INDEX \"idx_sakura\" (id,sakura)", (string) $alter);
    }

    public static function createQuery($conn = null): Query
    {
        return new Query($conn ?: new MockEscaper(), new BaseGrammar());
    }
}
