<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Trait OptionResolverTrait
 */
trait OptionResolverTrait
{
    protected array $options = [];

    /**
     * prepareDefaultOptions
     *
     * @param  array          $options
     * @param  callable|null  $handler
     *
     * @return  void
     */
    protected function resolveOptions(array $options = [], ?callable $handler = null): void
    {
        $resolver = new OptionsResolver();

        if ($handler) {
            $handler($resolver);
        }

        $this->options = $resolver->resolve($options);
    }

    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    public function setOption(string $name, $value): static
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }
}
