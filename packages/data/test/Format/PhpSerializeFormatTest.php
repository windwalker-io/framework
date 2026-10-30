<?php

declare(strict_types=1);

namespace Windwalker\Data\Test\Format;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Format\PhpSerializeFormat;

class PhpSerializeFormatTest extends TestCase
{
    #[Test]
    public function itSerializesAndRestoresNestedData(): void
    {
        $format = new PhpSerializeFormat;
        $data = ['flower' => 'sakura', 'meta' => ['count' => 3]];
        $serialized = $format->dump($data);

        self::assertSame(serialize($data), $serialized);
        self::assertSame($data, $format->parse($serialized));
    }

    #[Test]
    public function itPassesAllowedClassesOptionsToUnserialize(): void
    {
        $format = new PhpSerializeFormat;
        $serialized = serialize(['flower' => 'sakura']);

        self::assertSame(
            ['flower' => 'sakura'],
            $format->parse($serialized, ['allowed_classes' => false])
        );
    }
}
