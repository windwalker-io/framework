<?php

declare(strict_types=1);

namespace Windwalker\Language\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Language\LanguageNormalizer;

/**
 * Test class of LanguageNormalize
 *
 * @since 2.0
 */
class LanguageNormalizeTest extends TestCase
{
    public static function normalizeKeyProvider(): array
    {
        return [
            [
                'foo_bar',
                'foo.bar',
            ],
            [
                'flower-sakura-flower',
                'flower.sakura.flower',
            ],
            [
                'FLOWER_SAKURA_FLOWER',
                'flower.sakura.flower',
            ],
            [
                'Lorem ipsum dolor sit amet, consectetur.',
                'lorem.ipsum.dolor.sit.amet.consectetur',
            ],
            [
                '--test-foo.bar/yoo\\go{play}test[fly]--',
                'test.foo.bar.yoo.go.play.test.fly',
            ],
            [
                '雲彩裡，許是懺悔 THe B612 只有用心靈，一個人才能看得很清楚',
                'the.b612',
            ],
        ];
    }

    #[Test]
    public function toBCP47NormalizesLanguageAndRegionCase(): void
    {
        self::assertEquals('en-GB', LanguageNormalizer::toBCP47('en_gb'));
        self::assertEquals('en-GB', LanguageNormalizer::toBCP47('EN_GB'));
        self::assertEquals('en-GB', LanguageNormalizer::toBCP47('en-gb'));
        self::assertEquals('en-GB', LanguageNormalizer::toBCP47('EN-gB'));
    }

    #[Test]
    #[DataProvider('normalizeKeyProvider')]
    public function normalizeConvertsTextToDottedLowercaseKey(string $origin, string $expected): void
    {
        self::assertEquals($expected, LanguageNormalizer::normalize($origin));
    }

    #[Test]
    public function toISO15897UsesUnderscoreSeparator(): void
    {
        self::assertEquals('en_GB', LanguageNormalizer::toISO15897('en-gb'));
        self::assertEquals('zh_TW', LanguageNormalizer::toISO15897('ZH_tw'));
    }

    #[Test]
    public function normalizeLangCodeKeepsLanguageOnlyCodeWithoutRegion(): void
    {
        self::assertEquals('en', LanguageNormalizer::normalizeLangCode('EN'));
    }

    #[Test]
    public function sanitizeRemovesSpacesQuotesDotsAndSlashes(): void
    {
        self::assertEquals('enGB', LanguageNormalizer::sanitize("en #GB'\"./\\\\"));
    }

    #[Test]
    #[DataProvider('shortLangCodeProvider')]
    public function shortLangCodeReducesCodeToShortForm(string $code, string $expected): void
    {
        self::assertEquals($expected, LanguageNormalizer::shortLangCode($code));
    }

    public static function shortLangCodeProvider(): array
    {
        return [
            'language only' => ['EN', 'en'],
            'same language and region' => ['fr-FR', 'fr'],
            'different region' => ['en-gb', 'en_GB'],
            'underscore input' => ['zh_tw', 'zh_TW'],
        ];
    }

    #[Test]
    public function extractSplitsCodeIntoLanguageAndRegion(): void
    {
        self::assertSame(['en', 'GB'], LanguageNormalizer::extract('en_GB'));
        self::assertSame(['en', null], LanguageNormalizer::extract('en'));
    }
}
