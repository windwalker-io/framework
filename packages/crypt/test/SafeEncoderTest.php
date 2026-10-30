<?php

declare(strict_types=1);

namespace Windwalker\Crypt\Test;

use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Base32Hex;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Base64DotSlash;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\ConstantTime\Hex;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use const Windwalker\Crypt\ENCODER_BASE32;
use const Windwalker\Crypt\ENCODER_BASE32HEX;
use const Windwalker\Crypt\ENCODER_BASE64;
use const Windwalker\Crypt\ENCODER_BASE64DOTSLASH;
use const Windwalker\Crypt\ENCODER_BASE64URLSAFE;
use const Windwalker\Crypt\ENCODER_HEX;
use const Windwalker\Crypt\ENCODER_RAW;

use Windwalker\Crypt\SafeEncoder;

class SafeEncoderTest extends TestCase
{
    #[Test]
    #[DataProvider('encoderProvider')]
    public function encodeAndDecodeRoundTripBinaryData(string $encoder): void
    {
        $data = "\x00binary\xff:data";

        self::assertSame($data, SafeEncoder::decode($encoder, SafeEncoder::encode($encoder, $data)));
    }

    public static function encoderProvider(): array
    {
        return [
            'hex' => [ENCODER_HEX],
            'base32' => [ENCODER_BASE32],
            'base32hex' => [ENCODER_BASE32HEX],
            'base64' => [ENCODER_BASE64],
            'base64url' => [ENCODER_BASE64URLSAFE],
            'base64dotslash' => [ENCODER_BASE64DOTSLASH]
        ];
    }

    #[Test]
    #[DataProvider('encoderClassProvider')]
    public function chooseEncoderMapsKnownEncoders(string $encoder, string $class): void
    {
        self::assertSame($class, SafeEncoder::chooseEncoder($encoder));
    }

    public static function encoderClassProvider(): array
    {
        return [
            'hex' => [ENCODER_HEX, Hex::class],
            'base32' => [ENCODER_BASE32, Base32::class],
            'base32hex' => [ENCODER_BASE32HEX, Base32Hex::class],
            'base64' => [ENCODER_BASE64, Base64::class],
            'base64url' => [ENCODER_BASE64URLSAFE, Base64UrlSafe::class],
            'base64dotslash' => [ENCODER_BASE64DOTSLASH, Base64DotSlash::class]
        ];
    }

    #[Test]
    public function callablesCanEncodeAndDecodeAndRawDecoderPassesDataThrough(): void
    {
        self::assertSame('secret', SafeEncoder::decode(
            static fn(string $value): string => strtolower($value),
            SafeEncoder::encode(static fn(string $value): string => strtoupper($value), 'secret')
        ));
        self::assertSame('raw:data', SafeEncoder::decode(ENCODER_RAW, 'raw:data'));
    }
}
