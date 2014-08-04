<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Crypt;

use Windwalker\Crypt\Cipher\CipherInterface;

/**
 * The Crypt class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class Crypt
{
	/**
	 * Property cipher.
	 *
	 * @var CipherInterface
	 */
	protected $cipher;

	/**
	 * Property key.
	 *
	 * @var  KeyInterface
	 */
	protected $key;

	/**
	 * Property public.
	 *
	 * @var  string
	 */
	protected $public;

	/**
	 * Property private.
	 *
	 * @var  null|string
	 */
	protected $private;

	/**
	 * Class init.
	 *
	 * @param CipherInterface $cipher
	 * @param string          $private
	 * @param string          $public
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(CipherInterface $cipher, $private = null, $public = null)
	{
		$this->cipher = $cipher;
		$this->public = $public;
		$this->private  = $private ? : md5('ɹǝʞlɐʍpuıʍ');

		if (!is_string($this->private))
		{
			throw new \InvalidArgumentException('Public key should be string');
		}
	}

	/**
	 * encrypt
	 *
	 * @param string $string
	 *
	 * @return  string
	 */
	public function encrypt($string)
	{
		return $this->cipher->encrypt($string, $this->private, $this->public);
	}

	/**
	 * decrypt
	 *
	 * @param string $string
	 *
	 * @return  string
	 */
	public function decrypt($string)
	{
		return $this->cipher->decrypt($string, $this->private, $this->public);
	}

	/**
	 * Generate random bytes.
	 *
	 * @param   integer  $length  Length of the random data to generate
	 *
	 * @return  string  Random binary data
	 *
	 * @since   1.0
	 */
	public static function genRandomBytes($length = 16)
	{
		$sslStr = '';

		/*
		 * If a secure randomness generator exists use it.
		 */
		if (function_exists('openssl_random_pseudo_bytes'))
		{
			$sslStr = openssl_random_pseudo_bytes($length, $strong);

			if ($strong)
			{
				return $sslStr;
			}
		}

		/*
		 * Collect any entropy available in the system along with a number
		 * of time measurements of operating system randomness.
		 */
		$bitsPerRound = 2;
		$maxTimeMicro = 400;
		$shaHashLength = 20;
		$randomStr = '';
		$total = $length;

		// Check if we can use /dev/urandom.
		$urandom = false;
		$handle = null;

		if (@is_readable('/dev/urandom'))
		{
			$handle = @fopen('/dev/urandom', 'rb');

			if ($handle)
			{
				$urandom = true;
			}
		}

		while ($length > strlen($randomStr))
		{
			$bytes = ($total > $shaHashLength)? $shaHashLength : $total;
			$total -= $bytes;
			/*
			 * Collect any entropy available from the PHP system and filesystem.
			 * If we have ssl data that isn't strong, we use it once.
			 */
			$entropy = rand() . uniqid(mt_rand(), true) . $sslStr;
			$entropy .= implode('', @fstat(fopen(__FILE__, 'r')));
			$entropy .= memory_get_usage();
			$sslStr = '';

			if ($urandom)
			{
				stream_set_read_buffer($handle, 0);
				$entropy .= @fread($handle, $bytes);
			}
			else
			{
				/*
				 * There is no external source of entropy so we repeat calls
				 * to mt_rand until we are assured there's real randomness in
				 * the result.
				 *
				 * Measure the time that the operations will take on average.
				 */
				$samples = 3;
				$duration = 0;

				for ($pass = 0; $pass < $samples; ++$pass)
				{
					$microStart = microtime(true) * 1000000;
					$hash = sha1(mt_rand(), true);

					for ($count = 0; $count < 50; ++$count)
					{
						$hash = sha1($hash, true);
					}

					$microEnd = microtime(true) * 1000000;
					$entropy .= $microStart . $microEnd;

					if ($microStart >= $microEnd)
					{
						$microEnd += 1000000;
					}

					$duration += $microEnd - $microStart;
				}

				$duration = $duration / $samples;

				/*
				 * Based on the average time, determine the total rounds so that
				 * the total running time is bounded to a reasonable number.
				 */
				$rounds = (int) (($maxTimeMicro / $duration) * 50);

				/*
				 * Take additional measurements. On average we can expect
				 * at least $bitsPerRound bits of entropy from each measurement.
				 */
				$iter = $bytes * (int) ceil(8 / $bitsPerRound);

				for ($pass = 0; $pass < $iter; ++$pass)
				{
					$microStart = microtime(true);
					$hash = sha1(mt_rand(), true);

					for ($count = 0; $count < $rounds; ++$count)
					{
						$hash = sha1($hash, true);
					}

					$entropy .= $microStart . microtime(true);
				}
			}

			$randomStr .= sha1($entropy, true);
		}

		if ($urandom)
		{
			@fclose($handle);
		}

		return substr($randomStr, 0, $length);
	}

	/**
	 * Method to get property Public
	 *
	 * @return  string
	 */
	public function getPublic()
	{
		return $this->public;
	}

	/**
	 * Method to set property public
	 *
	 * @param   string $public
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setPublic($public)
	{
		$this->public = $public;

		return $this;
	}

	/**
	 * Method to get property Private
	 *
	 * @return  null|string
	 */
	public function getPrivate()
	{
		return $this->private;
	}

	/**
	 * Method to set property private
	 *
	 * @param   null|string $private
	 *
	 * @throws \InvalidArgumentException
	 * @return  static  Return self to support chaining.
	 */
	public function setPrivate($private)
	{
		$this->private = $private;

		if (!is_string($this->private))
		{
			throw new \InvalidArgumentException('Public key should be string');
		}

		return $this;
	}
}
 