<?php

declare(strict_types=1);

namespace Windwalker\DOM\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DOM\HTMLElement;

class DOMStringMapTest extends TestCase
{
    #[Test]
    public function mapsDataAttributesBetweenDashAndCamelCase(): void
    {
        $element = HTMLElement::new(
            'div',
            [
                'data-user-id' => '42',
                'data-ready' => 'yes',
                'title' => 'not data'
            ]
        );

        self::assertSame(['userId' => '42', 'ready' => 'yes'], $element->dataset->toArray());
        self::assertSame('42', $element->dataset->userId);
        self::assertTrue(isset($element->dataset->userId));
        self::assertFalse(isset($element->dataset->missingValue));
    }

    #[Test]
    public function writesAndRemovesDataAttributes(): void
    {
        $element = HTMLElement::new('div');
        $element->dataset->userId = 42;

        self::assertSame('42', $element->getAttribute('data-user-id'));
        self::assertSame(['userId' => '42'], $element->dataset->toArray());

        unset($element->dataset->userId);

        self::assertFalse($element->hasAttribute('data-user-id'));
        self::assertSame([], $element->dataset->toArray());
    }

    #[Test]
    public function serializesAnEmptyMapAsAnObject(): void
    {
        $dataset = HTMLElement::new('div')->dataset;

        self::assertSame('{}', json_encode($dataset, JSON_THROW_ON_ERROR));
    }
}
