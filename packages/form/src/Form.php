<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form;

use Attribute;
use Countable;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;
use OutOfBoundsException;
use ReflectionException;
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;
use Windwalker\Form\Attributes\Fieldset;
use Windwalker\Form\Attributes\NS;
use Windwalker\Form\Field\AbstractField;
use Windwalker\Form\Field\CompositeFieldInterface;
use Windwalker\Form\Renderer\FormRendererInterface;
use Windwalker\Form\Renderer\SimpleRenderer;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Classes\ObjectBuilderAwareTrait;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\Symbol;
use Windwalker\Utilities\TypeCast;

/**
 * The Form class.
 */
class Form implements IteratorAggregate, Countable, \ArrayAccess
{
    use ObjectBuilderAwareTrait;
    use OptionAccessTrait;

    public const FILTER_KEEP_DATA = 1 << 0;

    public const FILTER_USE_DEFAULT_VALUE = 1 << 1;

    protected string $namespace = '';

    protected array $bounded = [];

    protected ?Fieldset $fieldset = null;

    /**
     * @var Fieldset[]
     */
    protected array $fieldsets = [];

    protected array $namespaceStack = [];

    /**
     * Property fields.
     *
     * @var  AbstractField[]
     */
    protected array $fields = [];

    protected ?FormRendererInterface $renderer = null;

    protected ?AttributesResolver $attributeResolver = null;

    /**
     * Form constructor.
     *
     * @param  string                      $namespace
     * @param  array                       $options
     * @param  FormRendererInterface|null  $renderer
     */
    public function __construct(string $namespace = '', array $options = [], ?FormRendererInterface $renderer = null)
    {
        $this->setNamespace($namespace);

        $this->renderer = $renderer ?? new SimpleRenderer();

        $this->prepareOptions(
            [],
            $options
        );

        if (class_exists(AttributesResolver::class)) {
            $this->prepareAttributesResolver();
        }
    }

    /**
     * defineFormFields
     *
     * @param  string|FieldDefinitionInterface  $define
     *
     * @return  $this
     *
     * @throws ReflectionException
     */
    public function defineFormFields(FieldDefinitionInterface|string $define): static
    {
        if (is_string($define)) {
            $define = $this->getObjectBuilder()->createObject($define);
        }

        $define->define($this);

        return $this;
    }

    public function add(string $name, mixed $field, ?string $fieldset = null): AbstractField
    {
        [$namespace, $name] = FormNormalizer::extractNamespace($name);

        if (is_string($field) && class_exists($field)) {
            $field = $this->getObjectBuilder()->createObject($field, $name);
        }

        if (is_callable($field)) {
            $field = $field($this, $name);
        }

        if (!$field instanceof AbstractField) {
            throw new InvalidArgumentException(
                __METHOD__ . ' argument 2 should be sub class of AbstractField. '
                . $field . ' given.'
            );
        }

        $field->setName($name);

        if ($namespace) {
            $field->setNamespace($namespace);
        }

        return $this->addField($field, $fieldset);
    }

    public function addField(
        AbstractField $field,
        Fieldset|string|null $fieldset = null,
        string $namespace = ''
    ): AbstractField {
        if ($fieldset) {
            $fieldset = $this->fieldset($fieldset);
        } else {
            $fieldset = $this->fieldset;
        }

        if ($fieldset) {
            $field->setFieldset($fieldset->getName());
        }

        if (!$namespace) {
            $namespace = implode('/', $this->namespaceStack);
        }

        if ($namespace !== '') {
            $field->setNamespace($namespace);
        }

        $field->setForm($this);

        $this->fields[$field->getNamespaceName()] = $field;

        return $field;
    }

    public function addFields(array $fields, ?string $fieldset = null, string $namespace = ''): static
    {
        foreach ($fields as $field) {
            $this->addField($field, $fieldset, $namespace);
        }

        return $this;
    }

    /**
     * fill
     *
     * @param  mixed  $data
     * @param  bool   $decodeJson
     *
     * @return  $this
     */
    public function fill(mixed $data, bool $decodeJson = true): static
    {
        $data = TypeCast::toArray($data);

        if ($decodeJson) {
            $data = Arr::mapRecursive(
                $data,
                static function ($value) {
                    if (is_string($value) && is_json($value)) {
                        try {
                            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                        } catch (\JsonException) {
                            return $value;
                        }
                    }

                    return $value;
                }
            );
        }

        foreach ($this->fields as $name => $field) {
            $value = Arr::get($data, $name, '/');

            if ($value !== null) {
                $field->setValue($value);
            }
        }

        return $this;
    }

    /**
     * bind
     *
     * @param  array  $data
     *
     * @return  $this
     */
    public function bind(array &$data): static
    {
        $this->bounded = &$data;

        foreach ($this->fields as $name => $field) {
            $value = &Arr::get($data, $name, '/');

            $field->bindValue($value);

            // Break reference
            unset($value);
        }

        return $this;
    }

    public function getValues(): array
    {
        $values = [];

        foreach ($this->fields as $name => $field) {
            $values = Arr::set($values, $name, $field->getValue(), '/');
        }

        return $values;
    }

    public function &getBounded(): array
    {
        return $this->bounded;
    }

    /**
     * getFields
     *
     * @param  Symbol|string|null  $fieldset
     * @param  string              $namespace
     *
     * @return Generator
     *
     * @psalm-return AbstractField[]
     */
    public function getFields(Symbol|string|null $fieldset = null, string $namespace = ''): Generator
    {
        foreach ($this->fields as $k => $field) {
            if ($field->getFieldset() && Symbol::none()->is($fieldset)) {
                continue;
            }

            if ($fieldset !== null && $field->getFieldset() !== $fieldset && !Symbol::none()->is($fieldset)) {
                continue;
            }

            if ($namespace !== '' && $field->getNamespace() !== $namespace) {
                continue;
            }

            yield $k => $field;
        }
    }

    public function getField(string $namespace): ?AbstractField
    {
        return $this->fields[$namespace] ?? null;
    }

    public function hasField(string $namespace): bool
    {
        return isset($this->fields[$namespace]);
    }

    /**
     * removeField
     *
     * @param  string|AbstractField  $field  Field full namespace name or object.
     *
     * @return  $this
     */
    public function removeField(string|AbstractField $field): static
    {
        if (is_stringable($field)) {
            unset($this->fields[$field]);
        } else {
            $this->fields = array_filter($this->fields, fn($f) => $f !== $field);
        }

        return $this;
    }

    public function removeFields(Symbol|string|null $fieldset = null, string $namespace = ''): static
    {
        foreach ($this->getFields($fieldset, $namespace) as $field) {
            $this->removeField($field->getNamespaceName());
        }

        return $this;
    }

    public function wrap(?string $fieldset = null, ?string $namespace = null, ?callable $handler = null): static
    {
        if ($fieldset) {
            $fs = $this->fieldsets[$fieldset] ??= new Fieldset($fieldset, null);
            $this->fieldset = $fs;

            $fs->setForm($this);
        }

        if ($namespace) {
            $this->namespaceStack[] = $namespace;
        }

        if ($handler) {
            $this->register($handler);
        }

        if ($namespace) {
            array_pop($this->namespaceStack);
        }

        if ($fieldset) {
            $this->fieldset = null;
        }

        return $this;
    }

    public function register(callable $handler): static
    {
        if ($this->attributeResolver) {
            $this->attributeResolver->resolveCallable($handler)($this);
        }

        return $this;
    }

    public function fieldset(string $name, ?callable $handler = null): Fieldset
    {
        $this->wrap($name, null, $handler);

        return $this->fieldsets[$name];
    }

    public function fieldsetWithTitle(string $name, string $title, ?callable $handler = null): Fieldset
    {
        return $this->fieldset($name, $handler)
            ->title($title);
    }

    public function removeFieldset(string $name): static
    {
        unset($this->fieldsets[$name]);

        return $this;
    }

    /**
     * Wrap by namespace, use `/` to separate namespace.
     *
     * @param  string    $name
     * @param  callable  $handler
     *
     * @return  $this
     */
    public function ns(string $name, callable $handler): static
    {
        $this->wrap(null, $name, $handler);

        return $this;
    }

    /**
     * Alias of ns().
     *
     * @param  string    $name
     * @param  callable  $handler
     *
     * @return  static
     */
    public function group(string $name, callable $handler): static
    {
        return $this->ns($name, $handler);
    }

    public function filter(array $data, bool|int $options = 0): array
    {
        if (is_bool($options)) {
            $keepAllData = $options;
        } else {
            $keepAllData = (bool) ($options & static::FILTER_KEEP_DATA);
        }

        $filtered = $keepAllData ? $data : [];

        foreach ($this->fields as $name => $field) {
            if ($field instanceof CompositeFieldInterface) {
                $filtered = Arr::mergeRecursive(
                    $filtered,
                    $field->filter($data, $options)
                );
            } else {
                $name = $field->getNamespaceName(true);

                if (!Arr::has($data, $name, '/')) {
                    continue;
                }

                $value = Arr::get($data, $name, '/');
                $filtered = Arr::set($filtered, $name, $field->filter($value, $options), '/');
            }
        }

        return $filtered;
    }

    public function prepareStore(array $data): array
    {
        foreach ($this->fields as $name => $field) {
            if (!Arr::has($data, $name, '/')) {
                continue;
            }

            $value = Arr::get($data, $name, '/');

            $value = $field->prepareStore($value);

            $data = Arr::set($data, $name, $value, '/');
        }

        return $data;
    }

    /**
     * validate
     *
     * @param  array  $data
     *
     * @return  ResultSet
     */
    public function validate(array $data): ResultSet
    {
        $results = new ResultSet();

        foreach ($this->fields as $name => $field) {
            if ($field instanceof CompositeFieldInterface) {
                $value = $data;
            } else {
                $value = Arr::get($data, $field->getNamespaceName(true), '/');
            }

            $results->addResult($name, $field->validate($value));
        }

        return $results;
    }

    public function getViews(?string $fieldset = null, string $namespace = ''): array
    {
        $views = [];

        foreach ($this->getFields($fieldset, $namespace) as $name => $field) {
            $views = Arr::set($views, $field->getLabelName(), $field->renderView(), '/');
        }

        return $views;
    }

    public function renderField(string $namespace, array $options = []): string
    {
        $field = $this->getField($namespace);

        if (!$field) {
            throw new OutOfBoundsException("Field $namespace not found.");
        }

        return $field->render($options);
    }

    /**
     * renderFields
     *
     * @param  Symbol|string|null  $fieldset
     * @param  string|null         $namespace
     * @param  array               $options
     *
     * @return string
     */
    public function renderFields(
        Symbol|string|null $fieldset = null,
        ?string $namespace = '',
        array $options = []
    ): string {
        $output = '';

        foreach ($this->getFields($fieldset, (string) $namespace) as $field) {
            $output .= "\n" . $field->render($options);
        }

        return $output;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Generator
    {
        return $this->getFields();
    }

    /**
     * prepareAttributesResolver
     *
     * @return  void
     */
    protected function prepareAttributesResolver(): void
    {
        $this->attributeResolver = new AttributesResolver(
            [
                'form' => $this,
            ]
        );

        $this->attributeResolver->registerAttribute(
            Fieldset::class,
            Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | AttributeType::CALLABLE
        );

        $this->attributeResolver->registerAttribute(
            NS::class,
            Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | AttributeType::CALLABLE
        );
    }

    /**
     * @return AttributesResolver
     */
    public function getAttributeResolver(): AttributesResolver
    {
        return $this->attributeResolver;
    }

    /**
     * @return FormRendererInterface
     */
    public function getRenderer(): FormRendererInterface
    {
        return $this->renderer;
    }

    /**
     * @param  FormRendererInterface  $renderer
     *
     * @return  static  Return self to support chaining.
     */
    public function setRenderer(FormRendererInterface $renderer): static
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param  string  $namespace
     *
     * @return  static  Return self to support chaining.
     */
    public function setNamespace(string $namespace): static
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function appendNamespace(string $namespace): static
    {
        $this->namespace .= '/' . $namespace;
        $this->namespace = FormNormalizer::clearNamespace($this->namespace);

        return $this;
    }

    /**
     * @return Fieldset[]
     */
    public function getFieldsets(): array
    {
        return $this->fieldsets;
    }

    public function getFieldset(string $name): ?Fieldset
    {
        return $this->fieldsets[$name] ?? null;
    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     */
    public function count(): int
    {
        return count($this->fields);
    }

    public function countFields(Symbol|string|null $fieldset = null, string $namespace = ''): int
    {
        return iterator_count($this->getFields($fieldset, $namespace));
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return $this->hasField($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): ?AbstractField
    {
        return $this->getField($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->addField($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        $this->removeField($offset);
    }
}
