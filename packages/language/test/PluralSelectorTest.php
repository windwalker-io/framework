<?php

declare(strict_types=1);

namespace Windwalker\Language\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Language\PluralSelector;

class PluralSelectorTest extends TestCase
{
    protected PluralSelector $instance;

    #[\PHPUnit\Framework\Attributes\Before]
    protected function createSelector(): void
    {
        $this->instance = new PluralSelector();
    }

    public static function chooseProvider(): array
    {
        return [
            'exact match' => ['{0} none|{1} one|[2,*] many', 0, 'en-GB', 'none'],
            'exact match one' => ['{0} none|{1} one|[2,*] many', 1, 'en-GB', 'one'],
            'open upper range' => ['{0} none|{1} one|[2,*] many', 10, 'en-GB', 'many'],
            'open lower range' => ['[*,5] few|[6,10] some|{11} eleven', 3, 'en-GB', 'few'],
            'closed range' => ['[*,5] few|[6,10] some|{11} eleven', 8, 'en-GB', 'some'],
            'plain singular' => ['apple|apples', 1, 'en-GB', 'apple'],
            'plain plural' => ['apple|apples', 5, 'en-GB', 'apples'],
            'zero is plural in english' => ['apple|apples', 0, 'en-GB', 'apples'],
            'zero is singular in french' => ['pomme|pommes', 0, 'fr-FR', 'pomme'],
            'single segment' => ['apple', 5, 'en-GB', 'apple'],
            'no plural form for chinese' => ['蘋果|蘋果們', 5, 'zh-TW', '蘋果'],
            'missing index falls back to first' => ['a|b', 5, 'ru-RU', 'a'],
            'unmatched condition falls to plural rules' => ['{3} three|others', 4, 'en-GB', 'others'],
        ];
    }

    #[Test]
    #[DataProvider('chooseProvider')]
    public function chooseSelectsSegmentByConditionOrPluralRule(
        string $line,
        int $number,
        string $locale,
        string $expected
    ): void {
        self::assertSame($expected, $this->instance->choose($line, $number, $locale));
    }

    public static function pluralIndexProvider(): array
    {
        return [
            ['zh_TW', 5, 0],
            ['en_GB', 1, 0],
            ['en_GB', 2, 1],
            ['fr_FR', 0, 0],
            ['fr_FR', 2, 1],
            ['ru_RU', 1, 0],
            ['ru_RU', 3, 1],
            ['ru_RU', 11, 2],
            ['cs_CZ', 1, 0],
            ['cs_CZ', 3, 1],
            ['cs_CZ', 5, 2],
            ['ga_IE', 2, 1],
            ['ga_IE', 5, 2],
            ['lt_LT', 1, 0],
            ['lt_LT', 2, 1],
            ['lt_LT', 10, 2],
            ['sl_SI', 101, 0],
            ['sl_SI', 102, 1],
            ['sl_SI', 103, 2],
            ['sl_SI', 5, 3],
            ['mk_MK', 1, 0],
            ['mk_MK', 2, 1],
            ['mt_MT', 1, 0],
            ['mt_MT', 5, 1],
            ['mt_MT', 15, 2],
            ['mt_MT', 25, 3],
            ['lv_LV', 0, 0],
            ['lv_LV', 21, 1],
            ['lv_LV', 12, 2],
            ['pl_PL', 1, 0],
            ['pl_PL', 3, 1],
            ['pl_PL', 12, 2],
            ['cy_GB', 1, 0],
            ['cy_GB', 2, 1],
            ['cy_GB', 8, 2],
            ['cy_GB', 5, 3],
            ['ro_RO', 1, 0],
            ['ro_RO', 5, 1],
            ['ro_RO', 20, 2],
            ['ar_SA', 0, 0],
            ['ar_SA', 1, 1],
            ['ar_SA', 2, 2],
            ['ar_SA', 5, 3],
            ['ar_SA', 50, 4],
            ['ar_SA', 100, 5],
            ['xx_XX', 5, 0],
        ];
    }

    #[Test]
    #[DataProvider('pluralIndexProvider')]
    public function getPluralIndexFollowsLocaleRules(string $locale, int $number, int $expected): void
    {
        self::assertSame($expected, $this->instance->getPluralIndex($locale, $number));
    }
}
