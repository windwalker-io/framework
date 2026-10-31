<?php

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use Reflector;
use Windwalker\DI\Container;

/**
 * The AttributeHandler class.
 */
class AttributeHandler extends \Windwalker\Attributes\AttributeHandler
{
    /**
     * @inheritDoc
     */
    public function __construct(
        callable $handler,
        Reflector $reflector,
        ?object $object,
        AttributesResolver $resolver,
        array $options,
        public protected(set) Container $container,
    ) {
        parent::__construct($handler, $reflector, $object, $resolver, $options);
    }

    /**
     * @return  Container
     *
     * @codeCoverageIgnore
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @return  AttributesResolver
     *
     * @codeCoverageIgnore
     */
    public function getResolver(): AttributesResolver
    {
        return $this->resolver;
    }
}
