<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Attributes;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Attributes\Enum\Hidden;
use Windwalker\Utilities\Attributes\Enum\Title;
use Windwalker\Utilities\Contract\LanguageInterface;

class EnumAttributesTest extends TestCase
{
    #[Test]
    public function titleRequiresStringOrLangKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('must provide either string or trans key.');

        new Title();
    }

    #[Test]
    public function toReadableStringReturnsPlainStringWithoutLanguage(): void
    {
        self::assertSame('Plain', new Title('Plain')->toReadableString());
        self::assertSame('Plain', new Title('Plain', 'app.key')->toReadableString());
    }

    #[Test]
    public function toReadableStringTranslatesLangKeyWhenLanguageGiven(): void
    {
        $lang = $this->createMock(LanguageInterface::class);
        $lang->expects($this->once())->method('trans')->with('app.key', 'x')->willReturn('Translated');

        self::assertSame('Translated', new Title('Plain', 'app.key')->toReadableString($lang, 'x'));
    }

    #[Test]
    public function toReadableStringFallsBackToStringWhenOnlyStringGivenWithLanguage(): void
    {
        $lang = $this->createStub(LanguageInterface::class);

        self::assertSame('Plain', new Title('Plain')->toReadableString($lang));
    }

    #[Test]
    public function hiddenCanBeInstantiated(): void
    {
        self::assertInstanceOf(Hidden::class, new Hidden());
    }
}
