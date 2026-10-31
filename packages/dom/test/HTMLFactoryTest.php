<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\DOMElement;
use Windwalker\DOM\HTMLFactory;

#[IgnoreDeprecations]
class HTMLFactoryTest extends TestCase
{
    #[Test]
    public function createsHtmlElementsAndParsesText(): void
    {
        $element = HTMLFactory::element('div');
        $text = HTMLFactory::parse('hello');

        self::assertInstanceOf(DOMElement::class, $element);
        self::assertSame('<div></div>', $element->render());
        self::assertSame('hello', $text->textContent);
    }

    #[Test]
    public function parsesHtmlWithoutWrappingTextInASpan(): void
    {
        $node = HTMLFactory::parse('hello', 0);

        self::assertSame('hello', $node->textContent);
    }

    #[Test]
    public function serializesLegacyHtmlElements(): void
    {
        $element = HTMLFactory::element('div');
        $element->setAttribute('class', 'sample');
        $element->appendText('hello');

        self::assertSame('<div class="sample">hello</div>', HTMLFactory::saveHTML($element));
    }
}
