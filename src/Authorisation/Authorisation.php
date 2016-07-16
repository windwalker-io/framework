<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Authorisation;

/**
 * The Authorisation class.
 *
 * @since  {DEPLOY_VERSION}
 */
class Authorisation implements AuthorisationInterface
{
	/**
	 * Property policies.
	 *
	 * @var  PolicyInterface[]
	 */
	protected $policies = array();

	/**
	 * authorise
	 *
	 * @param string $policy
	 * @param mixed  $user
	 * @param mixed  $data
	 *
	 * @return  boolean
	 */
	public function authorise($policy, $user, $data = null)
	{
		if (!$this->hasPolicy($policy))
		{
			throw new \OutOfBoundsException(sprintf('Policy "%s" not exists', $policy));
		}
		
		$args = func_get_args();
		array_shift($args);

		return call_user_func_array(array($this->getPolicy($policy), 'authorise'), $args);
	}

	/**
	 * addPolicy
	 *
	 * @param   string                   $name
	 * @param   PolicyInterface|callable $handler
	 *
	 * @return  static
	 */
	public function addPolicy($name, $handler)
	{
		if (is_callable($handler))
		{
			$handler = new CallbackPolicy($handler);
		}

		if (!$handler instanceof PolicyInterface)
		{
			throw new \InvalidArgumentException('Not a valid policy, please give a callable or PolicyInterface');
		}

		$this->policies[$name] = $handler;

		return $this;
	}

	/**
	 * getPolicy
	 *
	 * @param   string  $name
	 *
	 * @return  PolicyInterface
	 */
	public function getPolicy($name)
	{
		if (isset($this->policies[$name]))
		{
			return $this->policies[$name];
		}

		return null;
	}

	/**
	 * registerPolicy
	 *
	 * @param PolicyProviderInterface $policy
	 *
	 * @return  static
	 */
	public function registerPolicyProvider(PolicyProviderInterface $policy)
	{
		$policy->register($this);

		return $this;
	}

	/**
	 * hasPolicy
	 *
	 * @param   string  $name
	 *
	 * @return  boolean
	 */
	public function hasPolicy($name)
	{
		return isset($this->policies[$name]);
	}

	/**
	 * Method to get property Policies
	 *
	 * @return  PolicyInterface[]
	 */
	public function getPolicies()
	{
		return $this->policies;
	}

	/**
	 * Method to set property policies
	 *
	 * @param   PolicyInterface[] $policies
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setPolicies($policies)
	{
		foreach ($policies as $name => $policy)
		{
			$this->addPolicy($name, $policy);
		}

		return $this;
	}
}
