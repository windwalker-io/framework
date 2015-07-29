<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Renderer\Test\Stub;

/**
 * The StubTwigExtension class.
 * 
 * @since  2.0
 */
class StubTwigExtension extends \Twig_Extension
{
	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'stub';
	}

	/**
	 * Returns a list of filters to add to the existing list.
	 *
	 * @return array An array of filters
	 */
	public function getFilters()
	{
		return array(
			new \Twig_SimpleFilter('armor', array($this, 'armor'))
		);
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return array An array of functions
	 */
	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('flower', array($this, 'flower'))
		);
	}

	/**
	 * Returns a list of global variables to add to the existing list.
	 *
	 * @return array An array of global variables
	 */
	public function getGlobals()
	{
		return array(
			'olive' => 'peace'
		);
	}

	/**
	 * flower
	 *
	 * @return  string
	 */
	public function flower()
	{
		return 'sakura';
	}

	/**
	 * armor
	 *
	 * @return  string
	 */
	public function armor()
	{
		return 'Iron Man';
	}
}
