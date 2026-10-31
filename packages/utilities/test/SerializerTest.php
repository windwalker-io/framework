<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use Laravel\SerializableClosure\SerializableClosure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Windwalker\closure;
use function Windwalker\serialize;
use function Windwalker\unserialize;

class SerializerTest extends TestCase
{
    #[Test]
    public function serializeAndUnserializeRoundTripPlainData(): void
    {
        $data = ['a' => 1, 'b' => [true, null]];

        self::assertSame(\serialize($data), serialize($data));
        self::assertSame($data, unserialize(serialize($data)));
    }

    #[Test]
    public function serializeAndUnserializeRoundTripClosure(): void
    {
        $closure = fn(int $n) => $n * 3;

        $result = unserialize(serialize($closure));

        self::assertInstanceOf(\Closure::class, $result);
        self::assertSame(12, $result(4));
    }

    #[Test]
    public function closureWrapsInSerializableClosure(): void
    {
        $wrapped = closure(fn() => 'ok');

        self::assertInstanceOf(SerializableClosure::class, $wrapped);
        self::assertSame('ok', ($wrapped->getClosure())());
    }
}
