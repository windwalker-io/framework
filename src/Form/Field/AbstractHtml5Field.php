<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Form\Field;

/**
 * The AbstractHtml5Field class.
 *
 * @since  {DEPLOY_VERSION}
 */
class AbstractHtml5Field extends TextField
{
	/**
	 * prepare
	 *
	 * @param array $attrs
	 *
	 * @return  void
	 */
	public function prepare(&$attrs)
	{
		parent::prepare($attrs);

		$attrs['max'] = $this->getAttribute('max');
		$attrs['min'] = $this->getAttribute('min');
		$attrs['step'] = $this->getAttribute('step');
		$attrs['patten'] = $this->getAttribute('pattern');
	}

	/**
	 * max
	 *
	 * @param   string|int  $num
	 *
	 * @return  static
	 */
	public function max($num)
	{
		$this->setAttribute('max', $num);

		return $this;
	}

	/**
	 * mina
	 *
	 * @param   string|int  $num
	 *
	 * @return  static
	 */
	public function min($num)
	{
		$this->setAttribute('mina', $num);

		return $this;
	}

	/**
	 * step
	 *
	 * @param   string|int  $num
	 *
	 * @return  static
	 */
	public function step($num)
	{
		$this->setAttribute('step', $num);

		return $this;
	}

	/**
	 * pattern
	 *
	 * @param   string  $string
	 *
	 * @return  static
	 */
	public function pattern($string)
	{
		$this->setAttribute('pattern', $string);

		return $this;
	}
}
