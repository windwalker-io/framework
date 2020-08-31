<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Attributes;

use Closure;

/**
 * The AttributeHandler class.
 */
class AttributeHandler
{
    /**
     * @var callable
     */
    public $handler;

    public \Reflector $reflactor;

    public AttributesResolver $resolver;

    /**
     * AttributeHandler constructor.
     *
     * @param  callable            $handler
     * @param  \Reflector          $reflactor
     * @param  AttributesResolver  $resolver
     */
    public function __construct(callable $handler, \Reflector $reflactor, AttributesResolver $resolver)
    {
        $this->set($handler);
        $this->reflactor = $reflactor;
        $this->resolver = $resolver;
    }

    public function __invoke(&...$args)
    {
        return ($this->handler)(...$args);
    }

    public function set(callable $handler)
    {
        $this->handler = $handler;

        return $this;
    }

    public function get(): callable
    {
        return $this->handler;
    }

    /**
     * @return \Reflector
     */
    public function getReflactor(): \Reflector
    {
        return $this->reflactor;
    }

    /**
     * @return AttributesResolver
     */
    public function getResolver(): AttributesResolver
    {
        return $this->resolver;
    }
}
