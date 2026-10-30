<?php

declare(strict_types=1);

namespace Windwalker\Data\Test\Format;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Format\JsonFormat;

class JsonFormatTest extends TestCase
{
    #[Test]
    public function itDumpsDataAndHonorsEncodingOptions(): void
    {
        $format = new JsonFormat;

        self::assertSame('{"name":"Sakura"}', $format->dump(['name' => 'Sakura']));
        self::assertSame(
            "{\n    \"name\": \"Sakura\"\n}",
            $format->dump(['name' => 'Sakura'], ['options' => JSON_PRETTY_PRINT])
        );
    }

    #[Test]
    public function itParsesAssociativeArraysAndObjects(): void
    {
        $format = new JsonFormat;

        self::assertSame(['name' => 'Sakura'], $format->parse(' {"name":"Sakura"} '));
        self::assertEquals(
            (object) ['name' => 'Sakura'],
            $format->parse('{"name":"Sakura"}', ['assoc' => false])
        );
    }
}
