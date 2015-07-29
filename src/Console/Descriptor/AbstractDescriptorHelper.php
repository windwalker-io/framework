<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Console\Descriptor;

/**
 * A descriptor helper to get different descriptor and render it.
 *
 * @since  2.0
 */
abstract class AbstractDescriptorHelper implements DescriptorHelperInterface
{
	/**
	 * Command descriptor.
	 *
	 * @var DescriptorInterface
	 *
	 * @since  2.0
	 */
	protected $CommandDescriptor;

	/**
	 * Option descriptor.
	 *
	 * @var DescriptorInterface
	 *
	 * @since  2.0
	 */
	protected $optionDescriptor;

	/**
	 * The class constructor.
	 *
	 * @param   DescriptorInterface  $CommandDescriptor  Command descriptor.
	 * @param   DescriptorInterface  $optionDescriptor   Option descriptor.
	 *
	 * @since   2.0
	 */
	public function __construct(DescriptorInterface $CommandDescriptor = null, DescriptorInterface $optionDescriptor = null)
	{
		$this->CommandDescriptor = $CommandDescriptor;
		$this->optionDescriptor  = $optionDescriptor;
	}

	/**
	 * Command descriptor getter.
	 *
	 * @return  DescriptorInterface
	 *
	 * @since   2.0
	 */
	public function getCommandDescriptor()
	{
		return $this->CommandDescriptor;
	}

	/**
	 * Command descriptor setter.
	 *
	 * @param   DescriptorInterface  $CommandDescriptor  Command descriptor.
	 *
	 * @return  $this Support chaining.
	 *
	 * @since   2.0
	 */
	public function setCommandDescriptor($CommandDescriptor)
	{
		$this->CommandDescriptor = $CommandDescriptor;

		return $this;
	}

	/**
	 * Option descriptor getter.
	 *
	 * @return \Windwalker\Console\Descriptor\DescriptorInterface
	 *
	 * @since   2.0
	 */
	public function getOptionDescriptor()
	{
		return $this->optionDescriptor;
	}

	/**
	 * Option descriptor setter.
	 *
	 * @param   DescriptorInterface  $optionDescriptor  Option descriptor.
	 *
	 * @return  $this  Support chaining.
	 *
	 * @since   2.0
	 */
	public function setOptionDescriptor($optionDescriptor)
	{
		$this->optionDescriptor = $optionDescriptor;

		return $this;
	}
}
