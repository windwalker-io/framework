<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Crypt\Test\Cipher;

use Windwalker\Crypt\Cipher\Des3Cipher;

/**
 * Test class of Cipher3DES
 *
 * @since 2.0
 */
class Des3CipherTest extends AbstractOpensslTestCase
{
    /**
     * Test instance.
     *
     * @var Des3Cipher
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
        parent::setUp();

        $this->instance = new Des3Cipher();
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
     *
     * @covers \Windwalker\Crypt\Cipher\Des3Cipher::getIVSize
     */
    public function testGetIVSize()
    {
        $this->assertEquals(8, $this->instance->getIVSize());
    }
}
