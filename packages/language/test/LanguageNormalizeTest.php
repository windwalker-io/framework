<?php

declare(strict_types=1);

namespace Windwalker\Language\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Language\LanguageNormalizer;

/**
 * Test class of LanguageNormalize
 *
 * @since 2.0
 */
class LanguageNormalizeTest extends TestCase
{
    /**
     * getToTagCases
     *
     * @return  array
     */
    public static function getToTagCases(): array
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

    /**
     * Method to test toBCP47().
     *
     * @return void
     *
     * @covers \Windwalker\Language\LanguageNormalizer::toBCP47
     */
    public function testToBCP47()
    {
        self::assertEquals('en-GB', LanguageNormalizer::toBCP47('en_gb'));
        self::assertEquals('en-GB', LanguageNormalizer::toBCP47('EN_GB'));
        self::assertEquals('en-GB', LanguageNormalizer::toBCP47('en-gb'));
        self::assertEquals('en-GB', LanguageNormalizer::toBCP47('EN-gB'));
    }

    /**
     * Method to test toLanguageKey().
     *
     * @param  string  $origin
     * @param  string  $expected
     *
     * @return void
     *
     * @covers       \Windwalker\Language\LanguageNormalizer::normalize
     *
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('getToTagCases')]
    public function testToLanguageKey($origin, $expected)
    {
        self::assertEquals($expected, LanguageNormalizer::normalize($origin));
    }
}
