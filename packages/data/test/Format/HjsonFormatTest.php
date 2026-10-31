<?php

declare(strict_types=1);

namespace Windwalker\Data\Test\Format;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Format\HjsonFormat;

class HjsonFormatTest extends TestCase
{
    #[Test]
    public function itDumpsAndParsesHjson(): void
    {
        $format = new HjsonFormat();
        $data = ['flower' => 'sakura', 'count' => 3];
        $hjson = $format->dump($data);

        self::assertSame($data, $format->parse($hjson));
    }

    #[Test]
    public function itParsesHjsonWithoutQuotesAroundKeys(): void
    {
        $format = new HjsonFormat();

        self::assertSame(['flower' => 'sakura'], $format->parse("flower: sakura\n"));
    }
}
