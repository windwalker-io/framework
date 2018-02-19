<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Crypt\Test;

use Windwalker\Crypt\Cipher\BlowfishCipher;
use Windwalker\Crypt\Cipher\PhpAesCipher;
use Windwalker\Crypt\Crypt;

/**
 * Test class of Crypt
 *
 * @since 2.0
 */
class CryptTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var Crypt
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
        $this->instance = new Crypt(new PhpAesCipher);
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
     * @covers \Windwalker\Crypt\Crypt::encrypt
     * @covers \Windwalker\Crypt\Crypt::verify
     */
    public function testEncrypt()
    {
        $encrypted = $this->instance->encrypt('windwalker');

        $this->assertTrue($this->instance->verify('windwalker', $encrypted));

        $crypt = new Crypt(new PhpAesCipher, 'flower');

        $encrypted = $crypt->encrypt('windwalker');

        $this->assertTrue($crypt->verify('windwalker', $encrypted, 'flower'));
    }

    /**
     * Method to test decrypt().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Crypt::decrypt
     */
    public function testDecrypt()
    {
        $hash = $this->instance->encrypt('windwalker');

        $this->assertEquals('windwalker', $this->instance->decrypt($hash));
    }

    /**
     * testDecryptLegacy
     *
     * @return  void
     */
    public function testDecryptLegacy()
    {
        $data      = 'windwalker';
        $key       = 'flower';
        $encrypted = 'VNEc5QYyPCpOUP5UjJnp07eZynRNKoQu';

        $crypt = new Crypt(new BlowfishCipher, $key);

        $decryped = $crypt->decrypt($encrypted);

        self::assertEquals($data, $decryped);
    }

    /**
     * Method to test getIv().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Crypt::getIv
     * @TODO   Implement testGetIv().
     */
    public function testGetIv()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setIv().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Crypt::setIv
     * @TODO   Implement testSetIv().
     */
    public function testSetIv()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getKey().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Crypt::getKey
     * @TODO   Implement testGetKey().
     */
    public function testGetKey()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setKey().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Crypt::setKey
     * @TODO   Implement testSetKey().
     */
    public function testSetKey()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
