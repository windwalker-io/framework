<?php

declare(strict_types=1);

namespace Windwalker\Data\Test\Format;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Format\XmlFormat;

class XmlFormatTest extends TestCase
{
    #[Test]
    public function itDumpsValuesUsingConfiguredRootAndNodeNames(): void
    {
        $format = new XmlFormat();
        $xml = $format->dump(
            ['name' => 'Sakura'],
            ['name' => 'Flower', 'nodeName' => 'item']
        );

        self::assertStringContainsString('<Flower>', $xml);
        self::assertStringContainsString('<item name="name" type="string">Sakura</item>', $xml);
    }

    #[Test]
    public function itParsesNestedTypedValues(): void
    {
        $format = new XmlFormat();
        $xml = '<Data>'
            . '<node name="name" type="string">Sakura</node>'
            . '<node name="count" type="integer">3</node>'
            . '<node name="meta" type="array"><node name="active" type="boolean">1</node></node>'
            . '</Data>';

        self::assertSame(
            ['name' => 'Sakura', 'count' => 3, 'meta' => ['active' => true]],
            $format->parse($xml)
        );
    }
}
