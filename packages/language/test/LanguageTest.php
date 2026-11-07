<?php

declare(strict_types=1);

namespace Windwalker\Language\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Windwalker\Language\Language;

/**
 * Test class of Language
 *
 * @since 2.0
 */
class LanguageTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var  Language
     */
    protected Language $instance;

    #[Before]
    protected function loadLanguageFixtures(): void
    {
        $this->instance = new Language();

        $this->instance->load(__DIR__ . '/fixtures/ini/en-GB.ini', 'ini', 'en-GB')
            ->load(__DIR__ . '/fixtures/ini/zh-TW.ini', 'ini', 'zh-TW');
    }

    #[Test]
    public function loadReadsFixtureFilesForEachLocale(): void
    {
        self::assertTrue($this->instance->has('WINDWALKER_LANGUAGE_TEST_FLOWER', 'en-GB'));
        self::assertTrue($this->instance->has('WINDWALKER_LANGUAGE_TEST_FLOWER', 'zh-TW'));
        self::assertEquals(['en-GB', 'Flower'], $this->instance->get('WINDWALKER_LANGUAGE_TEST_FLOWER', 'en-GB'));
        self::assertEquals(['zh-TW', '花'], $this->instance->get('WINDWALKER_LANGUAGE_TEST_FLOWER', 'zh-TW'));
        self::assertFalse($this->instance->has('A key not exists', 'en-GB', false));
    }

    #[Test]
    public function transTranslatesKeysInCurrentLocale(): void
    {
        $this->instance->setLocale('zh-TW');

        self::assertEquals('花', $this->instance->trans('WINDWALKER_LANGUAGE_TEST_FLOWER'));
        self::assertEquals('Olive', $this->instance->trans('WINDWALKER_LANGUAGE_TEST_Olive'));
        self::assertEquals('Sunflower', $this->instance->trans('Windwalker Language Test Sunflower'));

        self::assertEquals('A key not exists', $this->instance->trans('A key not exists'));
    }

    #[Test]
    public function transWrapsFoundAndMissingKeysInDebugMode(): void
    {
        $this->instance->setLocale('zh-TW');
        $this->instance->setDebug(true);

        self::assertEquals('**Sunflower**', $this->instance->trans('Windwalker Language Test Sunflower'));
        self::assertEquals('??A key not exists??', $this->instance->trans('A key not exists'));
    }

    #[Test]
    public function transRecordsMissingKeyAsOrphanInDebugMode(): void
    {
        $this->instance->setDebug(true);

        self::assertEquals('??A key not exists??', $this->instance->trans('A key not exists'));

        $orphans = $this->instance->getOrphans();

        self::assertArrayHasKey(
            'a.key.not.exists',
            $orphans
        );
    }

    #[Test]
    public function choiceSelectsStringByCountInDefaultLocale(): void
    {
        self::assertEquals('No Sunflower', $this->instance->choice('Windwalker Language Test Sunflower Choice', 0));
        self::assertEquals('Sunflower', $this->instance->choice('Windwalker Language Test Sunflower Choice', 1));
        self::assertEquals('Sunflowers', $this->instance->choice('Windwalker Language Test Sunflower Choice', 2));

    }

    #[Test]
    public function choiceSelectsStringByCountInCurrentLocale(): void
    {
        $this->instance->setLocale('zh-TW');

        self::assertEquals('沒有花', $this->instance->choice('Windwalker Language Test flower Choice', 0));
        self::assertEquals('花', $this->instance->choice('Windwalker Language Test flower Choice', 1));
        self::assertEquals('花', $this->instance->choice('Windwalker Language Test flower Choice', 2));
    }

    #[Test]
    public function choiceWrapsStringsAndMissingKeysInDebugMode(): void
    {
        $this->instance->setDebug(true);

        $this->instance->setLocale('zh-TW');

        self::assertEquals('**沒有花**', $this->instance->choice('Windwalker Language Test flower Choice', 0));
        self::assertEquals('**花**', $this->instance->choice('Windwalker Language Test flower Choice', 1));
        self::assertEquals('**花**', $this->instance->choice('Windwalker Language Test flower Choice', 2));
        self::assertEquals(
            '??Windwalker Language Test flower Choice not exists??',
            $this->instance->choice('Windwalker Language Test flower Choice not exists', 2)
        );
    }

    #[Test]
    public function transReplacesPlaceholdersWithArguments(): void
    {
        self::assertEquals(
            'The Sakura is beautiful~~~!!!',
            $this->instance->trans('WINDWALKER_LANGUAGE_TEST_BEAUTIFUL_FLOWER', 'Sakura')
        );
        self::assertEquals(
            'The Sunflower is beautiful~~~!!!',
            $this->instance->trans('WINDWALKER_LANGUAGE_TEST_BEAUTIFUL_FLOWER', 'Sunflower')
        );
    }

    #[Test]
    public function hasReportsWhetherKeyExists(): void
    {
        self::assertTrue($this->instance->has('Windwalker Language Test flower'));
        self::assertFalse($this->instance->has('Windwalker Language Test rose'));
    }

    #[Test]
    public function addStringMakesKeyTranslatable(): void
    {
        $this->instance->addString('windwalker language test rose', 'Rose');

        self::assertTrue($this->instance->has('Windwalker Language Test rose'));

        self::assertEquals('Rose', $this->instance->trans('WINDWALKER_LANGUAGE_TEST_ROSE'));
    }

    #[Test]
    public function addStringsMakesAllKeysTranslatable(): void
    {
        $strings = [
            'foo' => 'bar',
            'wind' => 'walker',
        ];

        $this->instance->addStrings($strings);

        self::assertEquals('bar', $this->instance->trans('foo'));
        self::assertEquals('walker', $this->instance->trans('wind'));
    }

    #[Test]
    public function extractedChildResolvesKeysWithNamespacePrefix(): void
    {
        $this->instance->setLocale('zh-TW');
        $child = $this->instance->extract('windwalker.language');
        $r = $child->trans('test.flower');

        self::assertEquals(
            '花',
            $r
        );
    }

    #[Test]
    public function setDebugWrapsTranslationsWithMarkers(): void
    {
        $this->instance->setDebug(true);

        self::assertEquals('**Sunflower**', $this->instance->trans('Windwalker Language Test Sunflower'));
        self::assertEquals('??A key not exists??', $this->instance->trans('A key not exists'));
    }

    #[Test]
    public function getOrphansReturnsMissingKeysWithCallerInfo(): void
    {
        $this->instance->setDebug(true);

        // Exists
        $this->instance->trans('Windwalker Language Test Sakura');

        // Not exists
        $this->instance->trans('Windwalker Language Test No exists flower');
        $this->instance->trans('A key not exists');
        $line = __LINE__ - 1;

        $orphans = $this->instance->getOrphans();

        self::assertEquals(['windwalker.language.test.no.exists.flower', 'a.key.not.exists'], array_keys($orphans));

        $caller = $orphans['a.key.not.exists']['caller'];

        $ref = new ReflectionMethod($this, __FUNCTION__);
        self::assertEquals(__METHOD__, $caller['class'] . '::' . $caller['function']);
        self::assertEquals(__FILE__, $caller['file']);
        self::assertEquals($line, $caller['line']);

        $called = $orphans['a.key.not.exists']['called'];
        self::assertEquals('Windwalker\Language\Language::trans', $called['class'] . '::' . $called['function']);
    }

    #[Test]
    public function getUsedReturnsOnlyExistingKeysTranslated(): void
    {
        // Exists
        $this->instance->trans('Windwalker Language Test Sakura');

        // Not exists
        $this->instance->trans('Windwalker Language Test No exists flower');
        $this->instance->trans('A key not exists');

        $used = $this->instance->getUsed();

        self::assertEquals(['windwalker.language.test.sakura'], $used);
    }

    #[Test]
    public function setLocaleNormalizesLocaleToBCP47(): void
    {
        self::assertEquals('en-GB', $this->instance->getLocale());

        $this->instance->setLocale('zh_tw');

        self::assertEquals('zh-TW', $this->instance->getLocale());
    }

    #[Test]
    public function normalizeUsesCustomHandlerWhenSet(): void
    {
        self::assertEquals('windwalker.is.good', $this->instance->normalize('Windwalker is good ~~~!!!'));

        $this->instance->setNormalizeHandler(
            function ($value) {
                return 'WINDWALKER-ROCKS';
            }
        );

        self::assertEquals('WINDWALKER-ROCKS', $this->instance->normalize('Windwalker is good ~~~!!!'));
    }

    #[Test]
    public function transReturnsRawWrapperValueUntouched(): void
    {
        self::assertEquals('Raw {key}', $this->instance->trans(\Windwalker\raw('Raw {key}')));
    }

    #[Test]
    public function choiceReturnsRawWrapperValueUntouched(): void
    {
        self::assertEquals('Raw {key}', $this->instance->choice(\Windwalker\raw('Raw {key}'), 2));
    }

    #[Test]
    public function replaceSubstitutesNamedPlaceholdersWithCaseVariants(): void
    {
        self::assertEquals(
            'hi sakura, SAKURA, Sakura',
            $this->instance->replace('hi :name, :NAME, :Name', ['name' => 'sakura'])
        );
    }

    #[Test]
    public function replacePrefersLongerPlaceholderNames(): void
    {
        self::assertEquals(
            'A B',
            $this->instance->replace(':foo :foobar', ['foo' => 'A', 'foobar' => 'B'])
        );
    }

    #[Test]
    public function replaceReturnsEmptyStringAsIs(): void
    {
        self::assertSame('', $this->instance->replace('', ['a' => 'b']));
    }

    #[Test]
    public function choiceReplacesCountPlaceholder(): void
    {
        $this->instance->addString('apples', '{0} no apples|{1} one apple|[2,*] :count apples');

        self::assertEquals('5 apples', $this->instance->choice('apples', 5));
    }

    #[Test]
    public function transFallsBackToFallbackLocaleWhenKeyMissingInCurrentLocale(): void
    {
        $this->instance->setLocale('zh-TW');

        self::assertEquals('Sunflower', $this->instance->trans('WINDWALKER_LANGUAGE_TEST_SUNFLOWER'));
    }

    #[Test]
    public function findDoesNotUseFallbackWhenDisabled(): void
    {
        self::assertSame([null, null], $this->instance->find('windwalker.language.test.sunflower', 'zh-TW', false));
    }

    #[Test]
    public function findLooksUpParentWhenChildHasNoString(): void
    {
        $child = new Language()->setParent($this->instance);

        self::assertSame(
            ['en-GB', 'Sakura'],
            $child->find('windwalker.language.test.sakura', 'en-GB')
        );
    }

    #[Test]
    public function loadReadsStringsFromFileForGivenLocale(): void
    {
        $lang = new Language();
        $lang->load(__DIR__ . '/fixtures/ini/zh-TW.ini', 'ini', 'zh-TW');
        $lang->setLocale('zh-TW');

        self::assertEquals('櫻花', $lang->trans('WINDWALKER_LANGUAGE_TEST_SAKURA'));
    }

    #[Test]
    public function loadFlattensNestedJsonAndPhpTranslations(): void
    {
        $json = new Language();
        $json->load(__DIR__ . '/fixtures/json/en-GB.json', 'json', 'en-GB');

        $php = new Language();
        $php->load(__DIR__ . '/fixtures/php/en-GB.php', 'php', 'en-GB');

        self::assertSame('Sakura', $json->trans('windwalker.language.test.sakura'));
        self::assertSame('Olive', $json->trans('windwalker.language.test.olive'));
        self::assertSame('Sakura', $php->trans('windwalker.language.test.sakura'));
    }

    #[Test]
    public function loadFlattensNestedYamlTranslationsWhenYamlIsAvailable(): void
    {
        if (!class_exists(\Symfony\Component\Yaml\Yaml::class)) {
            self::markTestSkipped('Symfony YAML is not installed.');
        }

        $lang = new Language();
        $lang->load(__DIR__ . '/fixtures/yaml/en-GB.yml', 'yaml', 'en-GB');

        self::assertSame('Sakura', $lang->trans('windwalker.language.test.sakura'));
        self::assertSame('Olive', $lang->trans('windwalker.language.test.olive'));
    }

    #[Test]
    public function getSelectorReturnsSameLazyInstance(): void
    {
        self::assertSame($this->instance->getSelector(), $this->instance->getSelector());
    }
}
