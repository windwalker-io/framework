<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\Format\DOMFormatter;
use Windwalker\DOM\Format\HTMLFormatter;

class FormatterTest extends TestCase
{
    #[Test]
    public function formatsNestedXmlAndMinifiesWhitespace(): void
    {
        self::assertSame(
            "<root>\n    <child>value</child>\n</root>",
            DOMFormatter::format('<root><child>value</child></root>')
        );
        self::assertSame(
            '<div><span>A B</span></div>',
            DOMFormatter::minify(" <div> \n <span>A  B</span> </div> ")
        );
    }

    #[Test]
    public function rejectsUnknownFormatterOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Unrecognized option.');

        new DOMFormatter(['unknown' => true]);
    }

    #[Test]
    public function preservesInlineElementsAndScriptBodiesWhileFormattingHtml(): void
    {
        $formatter = new HTMLFormatter();

        self::assertSame(
            "<div>\n    <script>if (a < b) { run(); }</script>\n    <span>one</span>\n</div>",
            $formatter->indent('<div><script>if (a < b) { run(); }</script><span>one</span></div>')
        );
    }

    #[Test]
    public function validatesAndChangesElementTypes(): void
    {
        $formatter = new HTMLFormatter();
        $formatter->setElementType('span', HTMLFormatter::ELEMENT_TYPE_BLOCK);

        self::assertSame(
            "<div>\n    <span>one</span>\n    <span>two</span>\n</div>",
            $formatter->indent('<div><span>one</span><span>two</span></div>')
        );

        $this->expectException(InvalidArgumentException::class);

        $formatter->setElementType('span', 99);
    }
}
