<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Inflector\EnglishInflector;
use Windwalker\Utilities\StrInflector;

/**
 * Test for the StrInflector class.
 *
 * @link   http://en.wikipedia.org/wiki/English_plural
 * @since  2.0
 */
class StrInflectorTest extends TestCase
{
    #[Test]
    public function toSingularInitializesEnglishInflector(): void
    {
        StrInflector::toSingular('words');

        self::assertInstanceOf(
            EnglishInflector::class,
            StrInflector::$inflector
        );
    }

    #[Test]
    public function pluralAndSingularFormsAreDetectedAndConverted(): void
    {
        self::assertSame('words', StrInflector::toPlural('word'));
        self::assertSame('word', StrInflector::toSingular('words'));
        self::assertTrue(StrInflector::isPlural('words'));
        self::assertFalse(StrInflector::isPlural('word'));
        self::assertTrue(StrInflector::isSingular('word'));
        self::assertFalse(StrInflector::isSingular('words'));
    }

    #[Test]
    public function possibleFormsReturnAllInflectionCandidates(): void
    {
        self::assertContains('indexes', StrInflector::getPossiblePluralize('index'));
        self::assertContains('person', StrInflector::getPossibleSingularize('people'));
    }
}
