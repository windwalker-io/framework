<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\HTMLElement;

class FunctionsTest extends TestCase
{
    #[Test]
    public function createsElementsWithNamespacedHelpers(): void
    {
        $element = \Windwalker\DOM\h('section', ['class' => 'main'], 'content');
        $div = \Windwalker\DOM\div(['id' => 'container']);
        $span = \Windwalker\DOM\span([], 'inline');
        $image = \Windwalker\DOM\img('image.png', ['alt' => 'Example']);

        self::assertInstanceOf(HTMLElement::class, $element);
        self::assertSame('content', $element->textContent);
        self::assertSame('container', $div->getAttribute('id'));
        self::assertSame('inline', $span->textContent);
        self::assertSame('image.png', $image->getAttribute('src'));
        self::assertSame('Example', $image->getAttribute('alt'));
    }

    #[Test]
    public function parsesHtmlWithTheNamespacedHelper(): void
    {
        $node = \Windwalker\DOM\parse_html('<strong>hello</strong>');

        self::assertSame('strong', $node->localName);
        self::assertSame('hello', $node->textContent);
    }
}
