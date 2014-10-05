<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Environment;

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
	 * Property uname.
	 *
	 * @var  string
	 */
	protected $uname = PHP_OS;

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
			$this->os = strtoupper(substr($this->uname, 0, 3));
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
			'HP-',
			'IRI',
			'LIN',
			'NET',
			'OPE',
			'SUN',
			'UNI'
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

	/**
	 * Method to set property os
	 *
	 * @param   string $os
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOS($os)
	{
		$this->os = $os;

		return $this;
	}

	/**
	 * Method to get property Uname
	 *
	 * @return  string
	 */
	public function getUname()
	{
		return $this->uname;
	}

	/**
	 * Method to set property uname
	 *
	 * @param   string $uname
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setUname($uname)
	{
		$this->uname = $uname;

		return $this;
	}
}
