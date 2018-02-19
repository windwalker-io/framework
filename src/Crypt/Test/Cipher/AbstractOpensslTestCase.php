<?php
/**
 * Part of windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Crypt\Test\Cipher;

use Windwalker\Crypt\Cipher\AbstractCipher;
use Windwalker\Crypt\CryptHelper;

/**
 * The AbstractCipherTestCase class.
 *
 * @since  2.0
 */
abstract class AbstractOpensslTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Property key.
     *
     * @var  string
     */
    protected $key = 'foo';

    /**
     * Test instance.
     *
     * @var AbstractCipher
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        if (!is_callable('openssl_encrypt')) {
            $this->markTestSkipped('Openssl Extension not available.');
        }
    }

    /**
     * Method to test encrypt().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Cipher\AbstractCipher::encrypt
     */
    public function testEncrypt()
    {
        $data = $this->instance->encrypt('windwalker', $this->key);

        $data = $this->instance->decrypt($data, $this->key);

        $this->assertEquals('windwalker', $data);

        // Use IV
        $iv = $this->instance->getIVKey();

        $data = $this->instance->encrypt('windwalker', $this->key, $iv);

        $data = $this->instance->decrypt($data, $this->key, $iv);

        $this->assertEquals('windwalker', $data);
    }

    /**
     * Method to test decrypt().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Cipher\AbstractCipher::decrypt
     */
    public function testDecrypt()
    {
        // Use IV
        $iv = $this->instance->getIVKey();

        $data = $this->instance->encrypt('windwalker', $this->key, $iv);

        $data = $this->instance->decrypt($data, $this->key, $iv);

        $this->assertEquals('windwalker', $data);
    }

    /**
     * Method to test getIVKey().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Cipher\AbstractCipher::getIVKey
     */
    public function testGetIVKey()
    {
        $this->assertEquals($this->instance->getIVSize(), strlen($this->instance->getIVKey()));
    }

    /**
     * rawEncrypt
     *
     * @param string  $data
     * @param string  $key
     * @param integer $type
     * @param integer $mode
     *
     * @return  string
     */
    protected function rawEncrypt($data, $key, $type, $mode)
    {
        $size = openssl_cipher_iv_length($type . '-' . $mode);

        $iv = CryptHelper::genRandomBytes($size);

        // Encrypt the data.
        $encrypted = openssl_encrypt($data, $type . '-' . $mode, $key, OPENSSL_RAW_DATA, $iv);

        return $iv . $encrypted;
    }
}
