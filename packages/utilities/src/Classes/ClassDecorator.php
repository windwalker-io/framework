<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

/**
 * Trait ClassDecorator
 */
class ClassDecorator
{
    protected ?object $innerObject = null;

    /**
     * DecoratorTrait constructor.
     *
     * @param  object  $innerObject
     */
    public function __construct(object $innerObject)
    {
        $this->innerObject = $innerObject;
    }

    public function __call(string $name, array $args): mixed
    {
        return $this->innerObject->$name(...$args);
    }
}
