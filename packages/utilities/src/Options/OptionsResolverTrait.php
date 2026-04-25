<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Options;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Trait OptionResolverTrait
 */
trait OptionsResolverTrait
{
    protected array $options = [];

    private static array $resolversByClass = [];

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
        $resolver = $this->getOptionsResolver();

        if ($handler) {
            $handler($resolver);
        }

        $this->options = $resolver->resolve($options);
    }

    protected function getOptionsResolver(): OptionsResolver
    {
        return new OptionsResolver();
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

    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }
}
