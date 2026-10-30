<?php

declare(strict_types=1);

namespace Windwalker\Data\Test\Format;

use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Format\PhpFormat;

class PhpFormatTest extends TestCase
{
    #[Test]
    public function itDumpsArraysAndCanGenerateAReturningPhpFile(): void
    {
        $format = new PhpFormat;
        $data = ['flower' => 'sakura', 'count' => 2];

        self::assertSame(
            "[\n    'flower' => 'sakura',\n    'count' => 2\n]",
            $format->dump($data)
        );
        self::assertSame(
            "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    'flower' => 'sakura',\n    'count' => 2\n];\n",
            $format->dump($data, ['return' => true])
        );
    }

    #[Test]
    public function parsingIsNotSupported(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIs('Currently does not support parse php array.');

        new PhpFormat()->parse('return [];');
    }
}
