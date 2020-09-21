<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI;

use Windwalker\Attributes\AttributesResolver as GlobalAttributesResolver;
use Windwalker\DI\Attributes\AttributeHandler;
use Windwalker\DI\Attributes\Inject;
use Windwalker\Utilities\Reflection\ReflectAccessor;

/**
 * The AttributesResolver class.
 */
class AttributesResolver extends GlobalAttributesResolver
{
    protected Container $container;

    /**
     * AttributesResolver constructor.
     *
     * @param  Container  $container
     * @param  array      $options
     */
    public function __construct(Container $container, array $options = [])
    {
        $this->container = $container;

        parent::__construct($options);
    }

    protected function prepareAttribute(object $attribute): void
    {
        $ref = new \ReflectionObject($attribute);

        foreach ($ref->getProperties() as $property) {
            $attrs = $property->getAttributes(Inject::class);

            foreach ($attrs as $attr) {
                ReflectAccessor::setValue($attribute, $property->getName(), $attr);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function createHandler(callable $getter, \Reflector $property): AttributeHandler
    {
        return new AttributeHandler($getter, $property, $this, $this->container);
    }
}
