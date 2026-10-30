<?php

declare(strict_types=1);

namespace Windwalker\Data\Test\Format;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Format\YamlFormat;

class YamlFormatTest extends TestCase
{
    #[Test]
    public function itDumpsAndParsesNestedValues(): void
    {
        $format = new YamlFormat;
        $data = ['flower' => 'sakura', 'meta' => ['count' => 3]];
        $yaml = $format->dump($data, ['indent' => 2]);

        self::assertStringContainsString('flower: sakura', $yaml);
        self::assertSame($data, $format->parse($yaml));
    }

    #[Test]
    public function itTrimsWhitespaceBeforeParsing(): void
    {
        $format = new YamlFormat;

        self::assertSame(['flower' => 'sakura'], $format->parse("flower: sakura\n"));
    }
}
