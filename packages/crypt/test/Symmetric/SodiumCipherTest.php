<?php

declare(strict_types=1);

namespace Windwalker\Crypt\Test\Symmetric;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SodiumException;
use UnexpectedValueException;
use Windwalker\Crypt\HiddenString;
use Windwalker\Crypt\Key;
use Windwalker\Crypt\Symmetric\SodiumCipher;

use const Windwalker\Crypt\ENCODER_RAW;
use const SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

/**
 * Test class of Cipher3DES
 *
 * @since 2.0
 */
class SodiumCipherTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var  SodiumCipher
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        $this->instance = new SodiumCipher();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return  void
     */
    protected function tearDown(): void {}

    /**
     * Method to test encrypt().
     *
     * @return  void
     *
     * @throws  SodiumException
     */
    public function testEncrypt()
    {
        $key = new Key('hello');

        $data = $this->instance->encrypt(new HiddenString('windwalker'), $key);

        $data = $this->instance->decrypt($data, $key);

        $this->assertEquals('windwalker', $data);
    }

    public function testEncryptWithString()
    {
        if (PHP_VERSION_ID < 80200) {
            $this->expectExceptionMessage('Do not use pure value for encrypting before PHP8.2');
        }

        $key = 'hello';

        $data = $this->instance->encrypt('windwalker', $key);

        $data = $this->instance->decrypt($data, $key);

        $this->assertEquals('windwalker', $data);
    }

    #[Test]
    public function refusesCiphertextWithInvalidAuthentication(): void
    {
        $key = new Key('test key');
        $cipher = new SodiumCipher();
        $encrypted = $cipher->encrypt('authenticated data', $key, ENCODER_RAW);
        $encrypted[32 + SODIUM_CRYPTO_SECRETBOX_NONCEBYTES] = chr(
            ord($encrypted[32 + SODIUM_CRYPTO_SECRETBOX_NONCEBYTES]) ^ 1
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageIs('Invalid message authentication code');

        $cipher->decrypt($encrypted, $key, ENCODER_RAW);
    }
}
