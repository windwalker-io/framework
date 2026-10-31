<?php

declare(strict_types=1);

namespace Windwalker\Filter\Test\Unicode;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Filter\Unicode\UnicodeHelper;

/**
 * @see UnicodeHelper
 */
class UnicodeHelperTest extends TestCase
{
    #[Test]
    public function latinToAsciiTransliteratesLowerAndUpperAccents(): void
    {
        self::assertSame('Cafe ss', UnicodeHelper::latinToAscii('Café ß'));
    }

    #[Test]
    public function latinToAsciiCaseArgumentLimitsTransliteration(): void
    {
        self::assertSame('é', UnicodeHelper::latinToAscii('é', 1));
        self::assertSame('e', UnicodeHelper::latinToAscii('é', -1));
        self::assertSame('E', UnicodeHelper::latinToAscii('É', 1));
        self::assertSame('É', UnicodeHelper::latinToAscii('É', -1));
    }

    #[Test]
    public function strtolowerLowercasesMultibyteText(): void
    {
        self::assertSame('éa中', UnicodeHelper::strtolower('ÉA中'));
    }

    #[Test]
    public function utf8strtolowerLowercasesWithoutMbstring(): void
    {
        self::assertSame('éa', UnicodeHelper::utf8strtolower('ÉA'));
        self::assertSame('', UnicodeHelper::utf8strtolower(''));
    }

    #[Test]
    public function toUnicodeDecodesUtf8IntoCodepoints(): void
    {
        self::assertSame([0x61, 0xE9, 0x4E2D, 0x1F600], UnicodeHelper::toUnicode('aé中😀'));
        self::assertSame([0x61], UnicodeHelper::toUnicode("\xEF\xBB\xBFa"));
    }

    #[Test]
    public function toUnicodeReturnsFalseForInvalidSequences(): void
    {
        set_error_handler(static fn() => true, E_USER_WARNING);

        try {
            self::assertFalse(UnicodeHelper::toUnicode("\xFF"));
            self::assertFalse(UnicodeHelper::toUnicode("\xC3a"));
            self::assertFalse(UnicodeHelper::toUnicode("\xC0\x80"));
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function fromUnicodeEncodesCodepointsIntoUtf8(): void
    {
        self::assertSame('aé中😀', UnicodeHelper::fromUnicode([0x61, 0xE9, 0x4E2D, 0x1F600]));
    }

    #[Test]
    public function fromUnicodeSkipsByteOrderMark(): void
    {
        self::assertSame('a', UnicodeHelper::fromUnicode([0xFEFF, 0x61]));
    }

    #[Test]
    public function fromUnicodeReturnsFalseForSurrogatesAndOutOfRangeCodepoints(): void
    {
        set_error_handler(static fn() => true, E_USER_WARNING);

        try {
            self::assertFalse(UnicodeHelper::fromUnicode([0xD800]));
            self::assertFalse(UnicodeHelper::fromUnicode([0x110000]));
        } finally {
            restore_error_handler();
        }
    }
}
