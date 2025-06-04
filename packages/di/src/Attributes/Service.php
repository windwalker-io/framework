<?php

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use Attribute;
use ReflectionParameter;
use ReflectionProperty;
use Windwalker\Core\Manager\AbstractManager;
use Windwalker\DI\Container;

/**
 * The Service class.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS)]
class Service extends Inject
{
    public ?string $name = null;

    public function __construct(
        ?string $id = null,
        bool|string $forceNewOrService = false,
        public int|array|null|\Closure $providedIn = null,
        ?string $tag = null,
    ) {
        if (is_string($forceNewOrService)) {
            $this->name = $forceNewOrService;

            $forceNewOrService = false;
        }

        parent::__construct($id, $forceNewOrService, $tag);
    }

    public function resolveInjectable(Container $container, ReflectionParameter|ReflectionProperty $reflector): mixed
    {
        // For Windwaker Core Service Manager
        // This flow is deprecated, but we keep it for backward compatibility.
        if ($this->name !== null && $this->id !== null && class_exists(AbstractManager::class)) {
            $managerClass = $this->id;

            if (is_subclass_of($managerClass, AbstractManager::class, true)) {
                /** @var AbstractManager $manager */
                $manager = $container->get($managerClass);

                return $manager->get($this->name);
            }
        }

        return parent::resolveInjectable($container, $reflector);
    }

    protected function createObject(Container $container, string $id): object
    {
        return $container->createSharedObject($id, tag: $this->tag);
    }
}
