<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Crypt\Test;

use Windwalker\Crypt\Cipher\CipherSimple;
use Windwalker\Crypt\CryptHelper;

/**
 * Test class of CipherSimple
 *
 * @since {DEPLOY_VERSION}
 */
class CipherSimpleTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var CipherSimple
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
		$this->instance = new CipherSimple;
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
	 * @covers Windwalker\Crypt\Cipher\McryptCipher::encrypt
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
	 * @covers Windwalker\Crypt\Cipher\McryptCipher::decrypt
	 */
	public function testDecrypt()
	{
		// Use IV
		$iv = CryptHelper::genRandomBytes(16);

		$data = $this->instance->encrypt('windwalker', $this->key, $iv);

		$ivSize = strlen($iv);

		$iv = substr($data, 0, $ivSize);

		$data = substr($data, $ivSize);

		$data = $this->instance->decrypt($data, $this->key, $iv);

		$this->assertEquals('windwalker', $data);
	}
}
