<?php

declare(strict_types=1);

namespace Windwalker\Data\Test\Format;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Format\TomlFormat;

class TomlFormatTest extends TestCase
{
    #[Test]
    public function itDumpsAndParsesNestedValues(): void
    {
        $format = new TomlFormat();
        $data = ['flower' => 'sakura', 'meta' => ['count' => 3]];
        $toml = $format->dump($data);

        self::assertStringContainsString('flower = "sakura"', $toml);
        self::assertSame($data, $format->parse($toml));
    }

    #[Test]
    public function itCanParseIntoAnObject(): void
    {
        $format = new TomlFormat();
        $result = $format->parse('flower = "sakura"', ['resultAsObject' => true]);

        self::assertEquals((object) ['flower' => 'sakura'], $result);
    }
}
