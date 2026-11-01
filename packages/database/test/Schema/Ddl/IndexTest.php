<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Schema\Ddl;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Schema\Ddl\Index;

/**
 * The IndexTest class.
 */
class IndexTest extends TestCase
{
    #[Test]
    public function expressionAddsFunctionalKeyPart(): void
    {
        $index = (new Index('idx_lower'))
            ->columns(['title'])
            ->expression('LOWER(email)');

        $columns = $index->getColumns();

        self::assertSame(['title', 'LOWER(email)'], array_keys($columns));
        self::assertNull($columns['title']->getIndexExpression());
        self::assertSame('LOWER(email)', $columns['LOWER(email)']->getIndexExpression());
    }
}
