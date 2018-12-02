<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt\Test\Cipher;

use Windwalker\Crypt\Cipher\SimpleCipher;
use Windwalker\Crypt\CryptHelper;

/**
 * Test class of CipherSimple
 *
 * @since 2.0
 */
class SimpleCipherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var SimpleCipher
     */
    protected $instance;

    /**
     * Property key.
     *
     * @var string
     */
    protected $key = 'windwalker';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->instance = new SimpleCipher();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
    }

    /**
     * Method to test encrypt().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Cipher\SimpleCipher::encrypt
     */
    public function testEncrypt()
    {
        $data = $this->instance->encrypt('windwalker', $this->key);

        $data = $this->instance->decrypt($data, $this->key);

        $this->assertEquals('windwalker', $data);

        // Use IV
        $iv = base64_encode(CryptHelper::genRandomBytes(16));

        $data = $this->instance->encrypt('windwalker', $this->key, $iv);

        $data = $this->instance->decrypt($data, $this->key, $iv);

        $this->assertEquals('windwalker', $data);
    }

    /**
     * Method to test decrypt().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Cipher\SimpleCipher::decrypt
     */
    public function testDecrypt()
    {
        $data = $this->instance->encrypt('windwalker', $this->key);

        $data = $this->instance->decrypt($data, $this->key);

        $this->assertEquals('windwalker', $data);
    }
}
