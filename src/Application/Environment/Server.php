<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Application\Environment;

/**
 * The Server class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class Server implements ServerInterface
{
	/**
	 * Property os.
	 *
	 * @var string
	 */
	protected $os;

	/**
	 * getOS
	 *
	 * @see  https://gist.github.com/asika32764/90e49a82c124858c9e1a
	 *
	 * @return  string
	 */
	public function getOS()
	{
		if (!$this->os)
		{
			// Detect the native operating system type.
			$this->os = strtoupper(substr(PHP_OS, 0, 3));
		}

		return $this->os;
	}

	/**
	 * isWin
	 *
	 * @return  bool
	 */
	public function isWin()
	{
		return $this->getOS() == 'WIN';
	}

	/**
	 * isMac
	 *
	 * @return  bool
	 */
	public function isMac()
	{
		return $this->getOS() == 'MAC';
	}

	/**
	 * isUnix
	 *
	 * @see  https://gist.github.com/asika32764/90e49a82c124858c9e1a
	 *
	 * @return  bool
	 */
	public function isUnix()
	{
		$unames = array(
			'CYG',
			'DAR',
			'FRE',
			'LIN',
			'NET',
			'OPE',
			'MAC'
		);

		return in_array($this->getOS(), $unames);
	}

	/**
	 * isLinux
	 *
	 * @return  bool
	 */
	public function isLinux()
	{
		return $this->getOS() == 'LIN';
	}
}
