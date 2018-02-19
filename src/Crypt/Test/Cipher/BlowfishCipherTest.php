<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Crypt\Test\Cipher;

use Windwalker\Crypt\Cipher\BlowfishCipher;

/**
 * Test class of CipherBlowfish
 *
 * @since 3.0
 */
class BlowfishCipherTest extends AbstractOpensslTestCase
{
    /**
     * Test instance.
     *
     * @var BlowfishCipher
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
        $this->instance = new BlowfishCipher;
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
     * Method to test getIVSize().
     *
     * @return void
     */
    public function testGetIVSize()
    {
        $this->assertEquals(8, $this->instance->getIVSize());
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
        $iv        = 'VNEc5QYyPCo=';
        $encrypted = 'VNEc5QYyPCpOUP5UjJnp07eZynRNKoQu';

        $decryped = $this->instance->decrypt($encrypted, $key, base64_decode($iv));

        self::assertEquals($data, $decryped);
    }
}
