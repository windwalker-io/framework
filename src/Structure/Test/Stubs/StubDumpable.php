<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Structure\Test\Stubs;

/**
 * The StubDumpable class.
 *
 * @since  2.1.1
 */
class StubDumpable
{
	public $foo = 'foo';

	protected $bar = 'bar';

	private $yoo = 'yoo';

	protected $data = array();

	protected $iterator;

	/**
	 * StubDumpable constructor.
	 *
	 * @param static $child
	 */
	public function __construct($child = null)
	{
		$this->iterator = new \ArrayIterator(array('wind' => 'walker'));

		$this->data = array(
			'self' => $this,
			'new' => $child,
			'flower' => array('sakura', 'rose')
		);
	}
}
