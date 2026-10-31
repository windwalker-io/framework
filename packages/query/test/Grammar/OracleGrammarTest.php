<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Grammar;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Query\Grammar\OracleGrammar;

class OracleGrammarTest extends AbstractGrammarTestCase
{
    protected static function grammarClass(): string
    {
        return OracleGrammar::class;
    }

    #[Test]
    public function upsertIsNotSupported(): void
    {
        $this->expectException(BadMethodCallException::class);

        self::compileUpsert(['id' => 1], ['id'], ['id']);
    }
}
