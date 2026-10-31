<?php

declare(strict_types=1);

namespace Windwalker\Http\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\FormData;

class FormDataTest extends TestCase
{
    #[Test]
    public function constructsFromNullArrayOrObject(): void
    {
        self::assertSame([], (new FormData())->all());
        self::assertSame(['a' => 1], (new FormData(['a' => 1]))->all());
        self::assertSame(['a' => 1], FormData::create((object) ['a' => 1])->all());
    }

    #[Test]
    public function wrapReturnsExistingInstanceOrCreatesNewOne(): void
    {
        $data = new FormData(['a' => 1]);

        self::assertSame($data, FormData::wrap($data));
        self::assertSame(['b' => 2], FormData::wrap(['b' => 2])->all());
    }

    #[Test]
    public function convertsToQueryString(): void
    {
        $data = FormData::create(['foo' => 'bar baz', 'list' => ['a', 'b']]);

        self::assertSame('foo=bar+baz&list%5B0%5D=a&list%5B1%5D=b', (string) $data);
    }

    #[Test]
    public function jsonSerializesStoredData(): void
    {
        self::assertSame('{"a":1}', json_encode(FormData::create(['a' => 1])));
    }
}
