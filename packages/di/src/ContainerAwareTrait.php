<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI;

/**
 * Trait ContainerAwareTrait
 */
trait ContainerAwareTrait
{
    protected ?Container $container = null;

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container ??= new Container();
    }

    /**
     * @param  Container|null  $container
     *
     * @return  static  Return self to support chaining.
     */
    public function setContainer(?Container $container): static
    {
        $this->container = $container;

        return $this;
    }
}
