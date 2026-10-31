<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Enum;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Contract\LanguageInterface;
use Windwalker\Utilities\Test\Stub\Enum\StubRichEnum;

class EnumRichTraitTest extends TestCase
{
    /**
     * @param  array<string, string>  $messages
     */
    protected function createLanguage(array $messages = []): LanguageInterface
    {
        $lang = $this->createStub(LanguageInterface::class);
        $lang->method('has')->willReturnCallback(fn(string $id) => isset($messages[$id]));
        $lang->method('trans')->willReturnCallback(fn(string $id) => $messages[$id] ?? 'missing:' . $id);

        return $lang;
    }

    #[Test]
    public function transBuildsKeyFromDashedEnumClassAndCaseName(): void
    {
        $lang = $this->createLanguage();

        self::assertSame(
            'missing:app.enum.windwalker-utilities-test-stub-enum-stub-rich-enum.ALPHA',
            StubRichEnum::ALPHA->trans($lang)
        );
    }

    #[Test]
    public function getTitleUsesTranslationWhenKeyExists(): void
    {
        $key = 'app.enum.windwalker-utilities-test-stub-enum-stub-rich-enum.BETA';
        $lang = $this->createLanguage([$key => 'Translated']);

        self::assertSame('Translated', StubRichEnum::BETA->getTitle($lang));
    }

    #[Test]
    public function getTitleFallsBackToTitleAttributeWhenTranslationMissing(): void
    {
        $lang = $this->createLanguage();

        self::assertSame('Alpha title', StubRichEnum::ALPHA->getTitle($lang));
        self::assertSame('Alpha title', StubRichEnum::ALPHA->getTitle());
    }

    #[Test]
    public function getTitleUsesAttributeLanguageKeyOnlyWithLanguage(): void
    {
        $lang = $this->createLanguage(['stub.beta' => 'Beta from attribute lang']);

        self::assertSame('Beta fallback', StubRichEnum::BETA->getTitle());
        self::assertSame('Beta from attribute lang', StubRichEnum::BETA->getTitle($lang));
    }

    #[Test]
    public function metaGettersReturnEmptyDefaultsWithoutAttributes(): void
    {
        self::assertSame('', StubRichEnum::GAMMA->getTitle());
        self::assertSame('', StubRichEnum::GAMMA->getIcon());
        self::assertSame('', StubRichEnum::GAMMA->getColor());
        self::assertSame([], StubRichEnum::GAMMA->getMeta());
    }

    #[Test]
    public function isHiddenDependsOnHiddenAttribute(): void
    {
        self::assertTrue(StubRichEnum::ALPHA->isHidden());
        self::assertFalse(StubRichEnum::BETA->isHidden());
    }

    #[Test]
    public function getTransItemsKeysByValueWithTranslatedTitles(): void
    {
        $lang = $this->createLanguage();

        self::assertSame(
            [
                'alpha' => 'Alpha title',
                'beta' => 'missing:stub.beta',
                'gamma_long' => '',
            ],
            StubRichEnum::getTransItems($lang)
        );
    }

    #[Test]
    public function maxLengthUsesLongestValue(): void
    {
        self::assertSame(10, StubRichEnum::maxLength());
    }

    #[Test]
    public function extendedHelpersExposeNamesAndRawValues(): void
    {
        self::assertSame(['ALPHA', 'BETA', 'GAMMA'], StubRichEnum::names());
        self::assertSame(
            ['ALPHA' => 'alpha', 'BETA' => 'beta', 'GAMMA' => 'gamma_long'],
            StubRichEnum::rawValues()
        );
        self::assertSame(['ALPHA', 'BETA', 'GAMMA'], array_keys(StubRichEnum::assocCases()));
        self::assertSame(StubRichEnum::BETA, StubRichEnum::assocCases()['BETA']);
        self::assertSame(StubRichEnum::BETA, StubRichEnum::values()['BETA']);
    }

    #[Test]
    public function wrapAndTryWrapAcceptValuesAndInstances(): void
    {
        self::assertSame(StubRichEnum::BETA, StubRichEnum::wrap('beta'));
        self::assertSame(StubRichEnum::BETA, StubRichEnum::wrap(StubRichEnum::BETA));
        self::assertSame(StubRichEnum::BETA, StubRichEnum::tryWrap('beta'));
        self::assertSame(StubRichEnum::BETA, StubRichEnum::tryWrap(StubRichEnum::BETA));
        self::assertNull(StubRichEnum::tryWrap(null));
        self::assertNull(StubRichEnum::tryWrap('nope'));
    }

    #[Test]
    public function fromTitleResolvesCaseOrReturnsNull(): void
    {
        self::assertSame(StubRichEnum::ALPHA, StubRichEnum::fromTitle('Alpha title'));
        self::assertSame(StubRichEnum::BETA, StubRichEnum::tryFromTitle('Beta fallback'));
        self::assertNull(StubRichEnum::tryFromTitle('nope'));
    }
}
