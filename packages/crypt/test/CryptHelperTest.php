<?php

declare(strict_types=1);

namespace Windwalker\Crypt\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SodiumException;
use Windwalker\Crypt\CryptHelper;

/**
 * Test class of CryptHelper
 *
 * @since 3.1.3
 */
#[CoversClass(CryptHelper::class)]
class CryptHelperTest extends TestCase
{
    /**
     * limitIntegerProvider
     *
     * @return  array
     */
    public function limitIntegerProvider(): array
    {
        return [
            [10, 5, 10, 15],
            [15, 20, 10, 15],
            [10, 5, 10, null],
            [20, 20, 10, null],
            [5, 5, null, 15],
            [15, 20, null, 15],
        ];
    }

    #[Test]
    public function testRepeatToLength(): void
    {
        $this->assertEquals('abcabc', CryptHelper::repeatToLength('abc', 5));
        $this->assertEquals('abc', CryptHelper::repeatToLength('abc', 2));
        $this->assertEquals('abcab', CryptHelper::repeatToLength('abc', 5, true));

        // No effect if length less than string
        $this->assertEquals('abc', CryptHelper::repeatToLength('abc', 1, true));
    }

    #[Test]
    public function testGetLength(): void
    {
        $this->assertEquals(7, CryptHelper::strlen('Ang Lee'));
        $this->assertEquals(6, CryptHelper::strlen('李安'));
    }

    /** @throws SodiumException */
    #[Test]
    public function derivesDeterministicBinaryKeysWithBlake2b(): void
    {
        $salt = str_repeat('s', 16);
        $first = CryptHelper::hkdfBlake2b('input key material', 80, 'encryption', $salt);

        self::assertSame(80, CryptHelper::strlen($first));
        self::assertSame($first, CryptHelper::hkdfBlake2b('input key material', 80, 'encryption', $salt));
        self::assertNotSame(
            $first,
            CryptHelper::hkdfBlake2b('input key material', 80, 'authentication', $salt)
        );
        self::assertNotSame(
            $first,
            CryptHelper::hkdfBlake2b('input key material', 80, 'encryption', str_repeat('o', 16))
        );
        self::assertSame('', CryptHelper::hkdfBlake2b('input key material', 0));
    }

    /** @throws SodiumException */
    #[Test]
    #[DataProvider('invalidHkdfLengthsProvider')]
    public function rejectsInvalidBlake2bKeyLengths(int $length): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Argument 2: Bad HKDF Digest Length');

        CryptHelper::hkdfBlake2b('input key material', $length);
    }

    public static function invalidHkdfLengthsProvider(): array
    {
        return [
            'negative length' => [-1],
            'length above maximum' => [255 * SODIUM_CRYPTO_GENERICHASH_KEYBYTES + 1]
        ];
    }

    #[Test]
    public function performsBinarySafeSubstringsAndCopies(): void
    {
        $binary = "\x00a\xffb";

        self::assertSame("\xffb", CryptHelper::substr($binary, 2));
        self::assertSame("\x00a", CryptHelper::substr($binary, 0, 2));
        self::assertSame('', CryptHelper::substr($binary, 0, 0));
        self::assertSame($binary, CryptHelper::strcpy($binary));
        self::assertSame('', CryptHelper::strcpy(''));
    }
}
