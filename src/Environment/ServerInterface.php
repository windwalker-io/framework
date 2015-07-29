<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Environment;

/**
 * The ServerInterface class.
 * 
 * @since  2.0
 */
interface ServerInterface
{
	/**
	 * isWin
	 *
	 * @return  bool
	 */
	public function isWin();

	/**
	 * isUnix
	 *
	 * @see  https://gist.github.com/asika32764/90e49a82c124858c9e1a
	 *
	 * @return  bool
	 */
	public function isUnix();

	/**
	 * isLinux
	 *
	 * @return  bool
	 */
	public function isLinux();
}
