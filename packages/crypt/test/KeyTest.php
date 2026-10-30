<?php

declare(strict_types=1);

namespace Windwalker\Crypt\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use const Windwalker\Crypt\ENCODER_HEX;

use Windwalker\Crypt\HiddenString;
use Windwalker\Crypt\Key;
use Windwalker\Crypt\SecretToolkit;

class KeyTest extends TestCase
{
    #[Test]
    public function wrapReturnsKeyAndPreservesExistingKeys(): void
    {
        $key = Key::wrap('hex:736563726574');
        $existingKey = new Key('existing-secret');

        self::assertSame('hex:736563726574', $key->get());
        self::assertSame('secret', Key::strip($key));
        self::assertSame($existingKey, Key::wrap($existingKey));
        self::assertSame('hidden-secret', Key::wrap(new HiddenString('hidden-secret'))->get());
    }

    #[Test]
    public function stripDecodesPrefixedKeysAndReturnsEmptyStringWhenConvertedToString(): void
    {
        $key = new Key(SecretToolkit::encode('secret', ENCODER_HEX));

        self::assertSame('secret', Key::strip($key));
        self::assertSame('secret', Key::strip('hex:736563726574'));
        self::assertSame('', (string) $key);
    }
}
