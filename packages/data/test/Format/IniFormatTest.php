<?php

declare(strict_types=1);

namespace Windwalker\Data\Test\Format;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Format\IniFormat;

class IniFormatTest extends TestCase
{
    #[Test]
    public function itDumpsGlobalValuesAndSections(): void
    {
        $format = new IniFormat;

        self::assertSame(
            "title=\"Sakura\"\nenabled=true\n\n[meta]\ndescription=\"spring\"",
            $format->dump([
                'title' => 'Sakura',
                'enabled' => true,
                'meta' => ['description' => 'spring']
            ])
        );
    }

    #[Test]
    public function itParsesSectionValuesWithTypedScanning(): void
    {
        $format = new IniFormat;

        self::assertSame(
            ['enabled' => true, 'meta' => ['count' => 3]],
            $format->parse(
                "enabled=true\n[meta]\ncount=3",
                ['process_section' => true, 'mode' => INI_SCANNER_TYPED]
            )
        );
    }
}
