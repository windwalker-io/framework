<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Attributes;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Attributes\Enum\Title;
use Windwalker\Utilities\Contract\LanguageInterface;

class EnumTitleTest extends TestCase
{
    #[Test]
    public function constructorRequiresStringOrLangKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('must provide either string or trans key');

        new Title();
    }

    #[Test]
    public function toReadableStringTranslatesLangKeyWhenLanguageGiven(): void
    {
        $lang = $this->createMock(LanguageInterface::class);
        $lang->expects($this->once())->method('trans')->with('foo.key', 'arg')->willReturn('Translated');

        self::assertSame('Translated', new Title('Plain', 'foo.key')->toReadableString($lang, 'arg'));
    }

    #[Test]
    public function toReadableStringFallsBackToPlainString(): void
    {
        $lang = $this->createStub(LanguageInterface::class);

        self::assertSame('Plain', new Title('Plain', 'foo.key')->toReadableString());
        self::assertSame('Plain', new Title('Plain')->toReadableString($lang));
    }
}
