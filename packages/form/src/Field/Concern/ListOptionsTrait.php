<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field\Concern;

use MyCLabs\Enum\Enum;
use Windwalker\DOM\DOMElement;
use Windwalker\DOM\HTMLFactory;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\Contract\LanguageInterface;
use Windwalker\Utilities\Enum\EnumTranslatableInterface;

/**
 * The ListOptionsTrait class.
 */
trait ListOptionsTrait
{
    /**
     * Property options.
     *
     * @var  DOMElement[]
     */
    protected array $options = [];

    /**
     * Property currentGroup.
     *
     * @var  string
     */
    protected ?string $currentGroup = null;

    protected function prepareListElement(DOMElement $input): DOMElement
    {
        foreach ($this->getOptions() as $key => $option) {
            $this->appendOption($input, $option, (string) $key);
        }

        return $input;
    }

    abstract protected function appendOption(DOMElement $select, DOMElement|array $option, ?string $group = null): void;

    /**
     * getOptions
     *
     * @return  array|DOMElement[]
     */
    public function getOptions(): array
    {
        return array_merge($this->options, $this->prepareOptions());
    }

    /**
     * setOptions
     *
     * @param  array|DOMElement[]  $options
     * @param  null|string         $group
     *
     * @return  static
     */
    public function setOptions(array $options, ?string $group = null): static
    {
        $this->resetOptions();
        $this->addOptions($options, $group);

        return $this;
    }

    /**
     * addOptions
     *
     * @param  array|DOMElement[]  $options
     * @param  null|string         $group
     *
     * @return  $this
     *
     * @since  3.5.19
     */
    public function addOptions(array $options, ?string $group = null): static
    {
        if ($group) {
            $this->options[$group] ??= [];

            $this->options[$group] = array_merge($this->options[$group], $options);
        } else {
            $this->options = array_merge($this->options, $options);
        }

        return $this;
    }

    /**
     * resetOptions
     *
     * @return  $this
     *
     * @since  3.5.19
     */
    public function resetOptions(): static
    {
        $this->options = [];

        return $this;
    }

    /**
     * register
     *
     * @param  callable  $handler
     *
     * @return  static
     *
     * @since  3.5.2
     */
    public function register(callable $handler): static
    {
        $handler($this);

        return $this;
    }

    public function registerFromEnums(iterable|string $enums, ?LanguageInterface $lang = null): static
    {
        if (is_string($enums)) {
            if (is_subclass_of($enums, Enum::class)) {
                $enums = $enums::values();
            } else {
                /** @var \UnitEnum $enums */
                $enums = $enums::cases();
            }
        }

        $options = [];

        foreach ($enums as $enum) {
            if ($enum instanceof EnumTranslatableInterface) {
                $options[$enum->getValue()] = $enum->getTitle($lang) ?: $enum->getKey();
            } elseif ($enum instanceof Enum) {
                $options[$enum->getValue()] = $enum->getKey();
            } elseif ($enum instanceof \UnitEnum) {
                $options[$enum->name ?? $enum->value] = $enum->name;
            }
        }

        return $this->registerOptions($options);
    }

    /**
     * registerOptions
     *
     * @param  iterable|string  $options
     * @param  callable|null    $handler
     *
     * @return  static
     *
     * @since  3.5.2
     */
    public function registerOptions(iterable|string $options, ?callable $handler = null): static
    {
        $isEnum = false;

        if (is_string($options)) {
            if ($isEnum = is_subclass_of($options, Enum::class)) {
                $options = array_flip($options::toArray());
            } else {
                TypeAssert::assert(
                    false,
                    'Options class must be Enum, {value} given.',
                    $options
                );
            }
        }

        // Todo: Remove list case
        $isList = false;

        foreach ($options as $name => $option) {
            if ($isList && !$isEnum) {
                // Option
                if ($handler) {
                    $handler($this, $option, null);
                } elseif ($option instanceof DOMElement) {
                    $this->addOption($option);
                } else {
                    $this->option((string) $option, (string) $option);
                }
            } elseif (is_array($option)) {
                foreach ($option as $opt) {
                    // Group
                    if ($handler) {
                        $handler($this, $opt, null, $name);
                    } elseif ($option instanceof DOMElement) {
                        $this->addOption($option, (string) $name);
                    } else {
                        $this->option((string) $opt, (string) $opt, [], (string) $name);
                    }
                }
            } else {
                // Option
                if ($handler) {
                    $handler($this, $option, $name);
                } elseif ($option instanceof DOMElement) {
                    $this->addOption($option, (string) $name);
                } else {
                    $this->option((string) $option, (string) $name);
                }
            }
        }

        return $this;
    }

    /**
     * addOption
     *
     * @param  DOMElement   $option
     * @param  string|null  $group
     *
     * @return  static
     */
    public function addOption(DOMElement $option, ?string $group = null): static
    {
        $options = [$option];

        if ($group === null) {
            $group = $this->currentGroup;
        }

        $this->addOptions($options, $group);

        return $this;
    }

    /**
     * option
     *
     * @param  string|null  $text
     * @param  string|null  $value
     * @param  array        $attrs
     * @param  string|null  $group
     *
     * @return static
     */
    public function option(
        \DOMNode|string|null $text = null,
        ?string $value = null,
        array $attrs = [],
        ?string $group = null
    ): static {
        $attrs['value'] = $value;

        $this->addOption(static::createOption($text, $value, $attrs), $group);

        return $this;
    }

    public static function createOption(
        \DOMNode|string|null $text = null,
        ?string $value = null,
        array $attrs = []
    ): DOMElement {
        $attrs['value'] = $value;

        return HTMLFactory::option($attrs, $text);
    }

    /**
     * optionGroup
     *
     * @param  string    $name
     * @param  callable  $callback
     *
     * @return  static
     */
    public function optionsGroup(string $name, callable $callback): static
    {
        $this->currentGroup = $name;

        $callback($this);

        $this->currentGroup = null;

        return $this;
    }

    /**
     * prepareOptions
     *
     * @return  array<DOMElement|mixed>
     */
    protected function prepareOptions(): array
    {
        return [];
    }
}
