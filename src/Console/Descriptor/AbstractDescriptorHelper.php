<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Console\Descriptor;

/**
 * A descriptor helper to get different descriptor and render it.
 *
 * @since  {DEPLOY_VERSION}
 */
abstract class AbstractDescriptorHelper implements DescriptorHelperInterface
{
	/**
	 * Command descriptor.
	 *
	 * @var DescriptorInterface
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $CommandDescriptor;

	/**
	 * Option descriptor.
	 *
	 * @var DescriptorInterface
	 *
	 * @since  {DEPLOY_VERSION}
	 */
	protected $optionDescriptor;

	/**
	 * The class constructor.
	 *
	 * @param   DescriptorInterface  $CommandDescriptor  Command descriptor.
	 * @param   DescriptorInterface  $optionDescriptor   Option descriptor.
	 *
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
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
	 * @since   {DEPLOY_VERSION}
	 */
	public function setOptionDescriptor($optionDescriptor)
	{
		$this->optionDescriptor = $optionDescriptor;

		return $this;
	}
}
