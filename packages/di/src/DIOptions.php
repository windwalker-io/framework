<?php

declare(strict_types=1);

namespace Windwalker\DI;

use Closure;
use Windwalker\Utilities\Options\RecordOptionsTrait;

class DIOptions
{
    use RecordOptionsTrait {
        wrap as parentWrap;
    }

    public function __construct(
        /**
         * Make a store definition singleton, always get same instance.
         */
        public ?bool $shared = null,
        /**
         * Make a store definition protected and unable to replace.
         */
        public ?bool $protected = null,
        /**
         * Make the store cache not share to children.
         * Every children Container will create new one even if parent has cache.
         */
        public ?bool $isolation = null,
        /**
         * Auto create dependencies when creating an object.
         */
        public ?bool $autowire = null,
        /**
         * Ignore all attributes when create object or call method.
         */
        public ?bool $ignoreAttributes = null,
        /**
         * Use PHP 8.4 Lazy Proxy to wrap resolved instance, the real object is created on first property access
         * (calling a method which does not touch properties will not initialize it).
         *
         * Lazy is skipped, and the normal eager flow is used, when the class:
         * - does not exist, is an interface, abstract class, trait or enum (not instantiable).
         * - is an internal class or extends one (e.g. ArrayObject, SplStack).
         *
         * For a nullable parameter, lazy is only used if the container has the service registered.
         *
         * Troubleshooting, errors thrown when the object is first accessed:
         * - DefinitionResolveException "Unable to initialize lazy object": the real instance is not
         *   compatible with the declared type. PHP requires the real instance to be the same class, or the proxy
         *   class to be a sub-class that adds no properties and overrides neither __destruct nor __clone.
         *   This happens when a service is bound to a sub-class which has extra properties or a custom
         *   __destruct()/__clone(), or when a factory returns another class. Fix: type-hint the concrete class,
         *   or disable lazy for it (`lazy: false`).
         * - DefinitionResolveException "Error when lazy creating object": the constructor or dependency
         *   resolving failed. The error is deferred until first access, see the previous exception for details.
         * - Constructor side effects run on first access instead of injection time.
         * - Extends registered by `whenCreating()->extend()` run on first access.
         *
         * @see \Windwalker\DI\Attributes\Lazy
         */
        public ?bool $lazy = null,
        /**
         * The service provided in these levels.
         */
        public int|array|Closure|null $providedIn = null,
        public bool $containerMerged = false,
    ) {}

    public static function wrap(mixed $values): static
    {
        if ($values instanceof static) {
            return clone $values;
        }

        if (is_int($values)) {
            return new static(
                shared: ($values & Container::SHARED) ? true : null,
                protected: ($values & Container::PROTECTED) ? true : null,
                isolation: ($values & Container::ISOLATION) ? true : null,
                autowire: ($values & Container::AUTO_WIRE) ? true : null,
                ignoreAttributes: ($values & Container::IGNORE_ATTRIBUTES) ? true : null,
            );
        }

        return static::parentWrap($values);
    }

    public function merge(array|object $values, bool $recursive = false, bool $ignoreNulls = false): static
    {
        if (is_object($values)) {
            $values = get_object_vars($values);
        }

        foreach ($values as $key => $value) {
            if ($ignoreNulls && $value === null) {
                continue;
            }

            $this->$key = $value;
        }

        return $this;
    }

    protected function normalizeKey(int|string $key): string|int
    {
        return $key;
    }
}
