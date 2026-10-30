<?php

declare(strict_types=1);

namespace Windwalker\Data\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\ValueObject;

class ValueObjectTest extends TestCase
{
    #[Test]
    public function itHydratesJsonAndNormalizesPropertyNames(): void
    {
        $value = new ValueObject('{"flower_name":"Sakura"}');

        self::assertSame('Sakura', $value->flowerName);
        self::assertSame(['flowerName' => 'Sakura'], $value->dump());
    }

    #[Test]
    public function itSupportsArrayAccessIterationAndStringConversion(): void
    {
        $value = new ValueObject(['flower' => 'Sakura']);

        self::assertArrayHasKey('flower', $value);
        self::assertSame('Sakura', $value['flower']);
        self::assertSame(['flower' => 'Rose'], $value->with(flower: 'Rose')->dump());

        $value['flower'] = 'Rose';
        self::assertSame(['flower' => 'Rose'], iterator_to_array($value));
        self::assertSame('{"flower":"Rose"}', (string) $value);

        unset($value['flower']);
        self::assertArrayNotHasKey('flower', $value);
    }
}
