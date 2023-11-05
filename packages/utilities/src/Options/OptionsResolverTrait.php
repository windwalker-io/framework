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
        if (OptionsResolverFactory::has(static::class)) {
            $this->options = $this->getOptionsResolver()->resolve($options);

            return;
        }

        $resolver = $this->getOptionsResolver();

        if ($handler) {
            $handler($resolver);
        }

        $this->options = $resolver->resolve($options);
    }

    protected function getOptionsResolver(): OptionsResolver
    {
        return OptionsResolverFactory::getByClass(static::class);
    }

    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    public function setOption(string $name, $value): static
    {
        $this->options[$name] = $value;

        // Re-check values
        return $this->setOptions($this->options);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): static
    {
        $this->options = OptionsResolverFactory::getByClass(static::class)->resolve($options);

        return $this;
    }
}
