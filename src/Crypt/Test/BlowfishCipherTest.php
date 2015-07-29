<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Crypt\Test;

use Windwalker\Crypt\Cipher\CipherBlowfish;

/**
 * Test class of CipherBlowfish
 *
 * @since 2.0
 */
class McryptBlowfishTest extends AbstractMcryptTestCase
{
	/**
	 * Test instance.
	 *
	 * @var CipherBlowfish
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

		$this->instance = new CipherBlowfish;
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
	 * @covers Windwalker\Crypt\Cipher\McryptCipher::getIVSize
	 */
	public function testGetIVSize()
	{
		$this->assertEquals(8, $this->instance->getIVSize());
	}
}
