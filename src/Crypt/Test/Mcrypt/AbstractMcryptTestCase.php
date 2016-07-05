<?php
/**
 * Part of windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Crypt\Test\Mcrypt;

use Windwalker\Crypt\Mcrypt\AbstractMcryptCipher;

/**
 * The AbstractCipherTestCase class.
 * 
 * @since  2.0
 */
abstract class AbstractMcryptTestCase extends \PHPUnit_Framework_TestCase
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
	 * @var AbstractMcryptCipher
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
		if (!extension_loaded('mcrypt'))
		{
			$this->markTestSkipped('Mcrypt Extension not available.');
		}
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
	 * @covers Windwalker\Crypt\Cipher\McryptCipher::decrypt
	 */
	public function testDecrypt()
	{
		// Use IV
		$iv = $this->instance->getIVKey();

		$data = $this->instance->encrypt('windwalker', $this->key, $iv);

		$ivSize = $this->instance->getIVSize();

		$iv = substr($data, 0, $ivSize);

		$data = substr($data, $ivSize);

		$data = $this->instance->decrypt($data, $this->key, $iv);

		$this->assertEquals('windwalker', $data);
	}

	/**
	 * Method to test getIVKey().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Crypt\Cipher\McryptCipher::getIVKey
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
		$size = mcrypt_get_iv_size($type, $mode);

		$iv = mcrypt_create_iv($size, MCRYPT_RAND);

		// Encrypt the data.
		$encrypted = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $data, $mode, $iv);

		return $iv . $encrypted;
	}
}
