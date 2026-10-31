<?php

declare(strict_types=1);

namespace Windwalker\Query\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Escaper;

/**
 * The EscaperTest class.
 */
class EscaperTest extends TestCase
{
    /**
     * @see  Escaper::tryQuote
     */
    #[Test]
    public function quoteWrapsValueWithQuotes(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Escaper::stripQuote
     */
    #[Test]
    public function stripQuoteRemovesQuotes(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Escaper::tryEscape
     */
    #[Test]
    public function escapeEscapesValue(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }
}
