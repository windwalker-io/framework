<?php

declare(strict_types=1);

namespace Windwalker\DI;

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
         * Use PHP 8.4 Lazy Proxy to wrap resolved instance.
         */
        public ?bool $lazy = null,
        /**
         * The service provided in these levels.
         */
        public int|array|null|\Closure $providedIn = null,
    ) {
    }

    public static function wrap(mixed $values): static
    {
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
}
