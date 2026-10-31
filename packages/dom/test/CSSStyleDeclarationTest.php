<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\HTMLElement;

class CSSStyleDeclarationTest extends TestCase
{
    #[Test]
    public function readsAndWritesPropertiesUsingCamelCaseNames(): void
    {
        $element = HTMLElement::new('div', ['style' => 'color: red; font-size: 12px']);
        $style = $element->style;

        self::assertSame('red', $style->color);
        self::assertSame('12px', $style->fontSize);

        $style->backgroundColor = 'blue';

        self::assertSame('color: red; font-size: 12px; background-color: blue', $element->getAttribute('style'));
        self::assertSame('blue', $style->getPropertyValue('background-color'));
    }

    #[Test]
    public function updatesAnExistingProperty(): void
    {
        $element = HTMLElement::new('div', ['style' => 'color: red']);

        $element->style->setProperty('color', 'blue');

        self::assertSame('color: blue', $element->getAttribute('style'));
    }

    #[Test]
    public function removesPropertiesAndReadsExternalStyleChanges(): void
    {
        $element = HTMLElement::new('div', ['style' => 'color: red; margin-top: 2px']);
        $style = $element->style;

        $style->removeProperty('color');

        self::assertSame('margin-top: 2px', $element->getAttribute('style'));

        $element->setAttribute('style', 'color: green');

        self::assertSame('green', $style->color);
        self::assertSame('color: green', (string) $style);
    }
}
