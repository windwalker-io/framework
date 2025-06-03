<?php

declare(strict_types=1);

namespace Windwalker\Authorization;

use InvalidArgumentException;
use OutOfBoundsException;

use function Windwalker\unwrap_enum;

/**
 * The Authorization class.
 *
 * @since  3.0
 */
class Authorization implements AuthorizationInterface
{
    /**
     * Property policies.
     *
     * @var  PolicyInterface[]
     */
    protected array $policies = [];

    public function authorize(string|\UnitEnum $policy, mixed $user, mixed ...$args): bool
    {
        $policy = unwrap_enum($policy);

        if (!$this->hasPolicy($policy)) {
            throw new OutOfBoundsException(sprintf('Policy "%s" not exists', $policy));
        }

        return $this->getPolicy($policy)?->authorize($user, ...$args) ?? false;
    }

    public function addPolicy(string|\UnitEnum $name, callable|PolicyInterface $handler): static
    {
        if (is_callable($handler)) {
            $handler = new CallbackPolicy($handler);
        }

        if (!$handler instanceof PolicyInterface) {
            throw new InvalidArgumentException('Not a valid policy, please give a callable or PolicyInterface');
        }

        $name = unwrap_enum($name);

        $this->policies[$name] = $handler;

        return $this;
    }

    public function getPolicy(string|\UnitEnum $name): ?PolicyInterface
    {
        $name = unwrap_enum($name);

        return $this->policies[$name] ?? null;
    }

    public function registerPolicyProvider(PolicyProviderInterface $policy): static
    {
        $policy->register($this);

        return $this;
    }

    public function hasPolicy(string|\UnitEnum $name): bool
    {
        $name = unwrap_enum($name);

        return isset($this->policies[$name]);
    }

    /**
     * Method to get property Policies
     *
     * @return  PolicyInterface[]
     */
    public function getPolicies(): array
    {
        return $this->policies;
    }

    /**
     * Method to set property policies
     *
     * @param  PolicyInterface[]  $policies
     *
     * @return  static  Return self to support chaining.
     */
    public function setPolicies(array $policies): static
    {
        foreach ($policies as $name => $policy) {
            $this->addPolicy($name, $policy);
        }

        return $this;
    }
}
