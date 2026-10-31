<?php

declare(strict_types=1);

namespace Windwalker\Crypt\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Crypt\SecretToolkit;

use const Windwalker\Crypt\ENCODER_BASE32;
use const Windwalker\Crypt\ENCODER_BASE32HEX;
use const Windwalker\Crypt\ENCODER_BASE64;
use const Windwalker\Crypt\ENCODER_BASE64DOTSLASH;
use const Windwalker\Crypt\ENCODER_BASE64URLSAFE;
use const Windwalker\Crypt\ENCODER_HEX;
use const Windwalker\Crypt\ENCODER_RAW;
use const Windwalker\Crypt\SECRET_128BIT;

class SecretToolkitTest extends TestCase
{
    #[Test]
    #[DataProvider('encoderProvider')]
    public function prefixedSecretsCanBeExtractedAndDecoded(string $encoder): void
    {
        $binary = "\x00secret\xff";
        $secret = SecretToolkit::encode($binary, $encoder);

        self::assertSame($encoder, SecretToolkit::getEncoder($secret));
        self::assertTrue(SecretToolkit::canDecode($secret));
        self::assertSame($binary, SecretToolkit::decode($secret));
        self::assertSame(explode(':', $secret, 2)[1], SecretToolkit::stripPrefix($secret));
        self::assertSame([$encoder, explode(':', $secret, 2)[1]], SecretToolkit::extract($secret));
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
    public function rawAndUnprefixedEncodingBehaveAsExpected(): void
    {
        $binary = "\x00secret\xff";

        self::assertSame($binary, SecretToolkit::encode($binary, ENCODER_RAW));
        self::assertSame($binary, SecretToolkit::decodeBy($binary, ENCODER_RAW));
        self::assertFalse(SecretToolkit::canDecode($binary));

        $unprefixed = SecretToolkit::encode($binary, ENCODER_HEX, false);

        self::assertSame(bin2hex($binary), $unprefixed);
        self::assertSame($binary, SecretToolkit::decodeBy($unprefixed, ENCODER_HEX));
        self::assertSame($binary, SecretToolkit::decodeIfHasPrefix($binary));
    }

    #[Test]
    public function prefixedValuesAreNotEncodedTwice(): void
    {
        $secret = SecretToolkit::encode('secret', ENCODER_HEX);

        self::assertSame($secret, SecretToolkit::encodeIfNoPrefix($secret));
        self::assertSame(
            SecretToolkit::encode('secret', ENCODER_BASE64URLSAFE),
            SecretToolkit::encodeIfNoPrefix('secret')
        );
    }

    #[Test]
    public function unknownPrefixesAreNotTreatedAsSecrets(): void
    {
        self::assertNull(SecretToolkit::getEncoder('unknown:secret'));
        self::assertFalse(SecretToolkit::canDecode('unknown:secret'));
        self::assertSame('unknown:secret', SecretToolkit::stripPrefix('unknown:secret'));
        self::assertSame('unknown:secret', SecretToolkit::decodeIfHasPrefix('unknown:secret'));
        self::assertNull(SecretToolkit::getEncoder('plain-secret'));
    }

    #[Test]
    public function extractingUnprefixedValuesFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Invalid secret string.');

        SecretToolkit::extract('plain-secret');
    }

    #[Test]
    public function generateSecretApisHonorRequestedLengthsAndPrefixes(): void
    {
        self::assertSame(9, strlen(SecretToolkit::genRawSecret(9)));
        self::assertSame(SECRET_128BIT, strlen(SecretToolkit::genRawSecret()));

        $prefixed = SecretToolkit::genSecret(12, ENCODER_HEX);
        $unprefixed = SecretToolkit::genSecretWithoutPrefix(7, ENCODER_HEX);

        self::assertSame(12, strlen(SecretToolkit::decode($prefixed)));
        self::assertSame('hex', SecretToolkit::getEncoder($prefixed));
        self::assertSame(7, strlen(SecretToolkit::decodeBy($unprefixed, ENCODER_HEX)));
        self::assertNull(SecretToolkit::getEncoder($unprefixed));
    }
}
