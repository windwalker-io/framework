<?php

declare(strict_types=1);

namespace Windwalker\Filter\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Filter\HtmlCleaner;

/**
 * @see HtmlCleaner
 */
class HtmlCleanerTest extends TestCase
{
    #[Test]
    #[DataProvider('badAttributeProvider')]
    public function isBadAttributeDetectsDangerousValues(string $name, string $value, bool $expected): void
    {
        self::assertSame($expected, HtmlCleaner::isBadAttribute([$name, $value]));
    }

    public static function badAttributeProvider(): array
    {
        return [
            'javascript url' => ['href', 'JavaScript:alert(1)', true],
            'vbscript url' => ['href', 'vbscript:x', true],
            'css expression in style' => ['style', 'width: expression(alert(1))', true],
            'expression in other attribute' => ['title', 'expression', false],
            'safe url' => ['href', 'https://example.com', false],
        ];
    }

    #[Test]
    public function removeStripsBlacklistedTags(): void
    {
        $cleaner = new HtmlCleaner();

        self::assertSame('<p>Hi</p>alert(1)', $cleaner->remove('<p>Hi</p><script>alert(1)</script>'));
    }

    #[Test]
    public function removeStripsBlacklistedAttributes(): void
    {
        $cleaner = new HtmlCleaner();

        $result = $cleaner->remove('<a href="x" onclick="evil()">A</a>');

        self::assertStringNotContainsString('onclick', $result);
        self::assertStringContainsString('href="x"', $result);
    }

    #[Test]
    public function removeWithWhitelistKeepsOnlyListedTags(): void
    {
        $cleaner = new HtmlCleaner(['b'], [], HtmlCleaner::USE_WHITE_LIST);

        $result = $cleaner->remove('<b>bold</b><i>italic</i>');

        self::assertSame('<b>bold</b>italic', $result);
    }

    #[Test]
    public function settersChangeCleanerMethods(): void
    {
        $cleaner = new HtmlCleaner()
            ->setTagsMethod(HtmlCleaner::USE_WHITE_LIST)
            ->setAttrMethod(HtmlCleaner::USE_WHITE_LIST)
            ->setXssMethod(0);

        self::assertSame(HtmlCleaner::USE_WHITE_LIST, $cleaner->getTagsMethod());
        self::assertSame(HtmlCleaner::USE_WHITE_LIST, $cleaner->getAttrMethod());
        self::assertSame(0, $cleaner->getXssMethod());
    }
}
