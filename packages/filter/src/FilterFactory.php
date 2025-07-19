<?php

declare(strict_types=1);

namespace Windwalker\Filter;

use OutOfRangeException;
use Windwalker\Filter\Rule\Absolute;
use Windwalker\Filter\Rule\Alnum;
use Windwalker\Filter\Rule\CastTo;
use Windwalker\Filter\Rule\Clamp;
use Windwalker\Filter\Rule\Cmd;
use Windwalker\Filter\Rule\DefaultValue;
use Windwalker\Filter\Rule\EmailAddress;
use Windwalker\Filter\Rule\IPAddress;
use Windwalker\Filter\Rule\IPV4;
use Windwalker\Filter\Rule\IPV6;
use Windwalker\Filter\Rule\Length;
use Windwalker\Filter\Rule\Negative;
use Windwalker\Filter\Rule\Range;
use Windwalker\Filter\Rule\RawValue;
use Windwalker\Filter\Rule\Regex;
use Windwalker\Filter\Rule\Required;
use Windwalker\Filter\Rule\UrlAddress;
use Windwalker\Filter\Rule\Words;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Classes\ObjectBuilderAwareTrait;
use Windwalker\Utilities\TypeCast;

/**
 * The FilterFactory class.
 *
 * Default filter syntaxes:
 * - abs
 * - alnum
 * - cmd
 * - email
 * - url
 * - words
 * - ip
 * - ipv4
 * - ipv6
 * - neg
 * - raw
 * - range(min=int, max=int)
 * - clamp(min=int, max=int)
 * - length(max=int, [utf8])
 * - regex(regex=string, type='match'|'replace')
 * - required
 * - default(value=mixed)
 * - func(callback)
 * - string([strict])
 * - int([strict])
 * - float([strict])
 * - array([strict])
 * - bool([strict])
 * - object([strict])
 */
class FilterFactory
{
    use ObjectBuilderAwareTrait;

    /**
     * @var callable[]
     */
    protected array $factories = [];

    /**
     * FilterFactory constructor.
     *
     * @param  callable[]  $factories
     */
    public function __construct(?array $factories = null)
    {
        if ($factories !== null) {
            $this->factories = $factories;
        } else {
            $this->prepareDefaultFactories();
        }
    }

    public function createNested(array $map, array $options = []): NestedFilter
    {
        return new NestedFilter($this->createMap($map, $options));
    }

    public function createMap(array $map, array $options = []): array
    {
        return Arr::mapRecursive($map, fn($syntax) => $this->createChainFromSyntax($syntax, $options));
    }

    /**
     * createChainFromSyntax
     *
     * @param  string|callable|FilterInterface|ValidatorInterface|array  $syntax
     * @param  array                                                     $options
     *
     * @return  FilterInterface|ValidatorInterface
     */
    public function createChainFromSyntax(mixed $syntax, array $options = []): FilterInterface|ValidatorInterface
    {
        if (is_string($syntax)) {
            $clauses = Arr::explodeAndClear('|', $syntax);
        } elseif (!is_array($syntax)) {
            $clauses = [$syntax];
        } else {
            $clauses = $syntax;
        }

        $chain = new ChainFilter();

        foreach ($clauses as $clause) {
            if (is_string($clause)) {
                if ($this->hasFactory($clause)) {
                    $filter = $this->createByFactory($clause, $options);
                } elseif (class_exists($clause)) {
                    $filter = $this->create($clause);
                } else {
                    $filter = $this->createFromSyntax($clause, $options);
                }
            } else {
                $filter = $this->create($clause);
            }

            $chain->addFilter($filter);
        }

        return $chain;
    }

    public function createFromSyntax(string $syntax, array &$options = []): FilterInterface|ValidatorInterface
    {
        preg_match('/(?P<type>[\w\\\\]+)(\((?P<params>.*)\))*/', $syntax, $matches);

        $type = $matches['type'] ?? '';
        $params = $matches['params'] ?? '';

        $type = trim($type);

        preg_match_all('/(\w+)(\s?[=:]\s?(\w+))?/', $params, $matches, PREG_SET_ORDER);

        $options = [];
        foreach ($matches as $match) {
            if (isset($match[1])) {
                $key = $match[1];
                $value = $match[3] ?? true;

                $options[$key] = $value;
            }
        }

        return $this->createByFactory($type, $options);
    }

    public function create($type, ...$args): FilterInterface|ValidatorInterface
    {
        if ($type instanceof FilterInterface || $type instanceof ValidatorInterface) {
            return $type;
        }

        if (is_callable($type)) {
            return new CallbackFilter($type);
        }

        if (class_exists($type)) {
            return $this->getObjectBuilder()->createObject($type, ...$args);
        }

        if ($args === []) {
            return $this->createChainFromSyntax($type);
        }

        return $this->createFromSyntax($type, ...$args);
    }

    public function createByFactory(string $type, array $options = []): FilterInterface|ValidatorInterface
    {
        return $this->create($this->getFactory($type)($options));
    }

    public function getFactory(string $type): callable
    {
        if (!isset($this->factories[$type])) {
            throw new OutOfRangeException("Filter type: $type not found.");
        }

        return $this->factories[$type];
    }

    public function hasFactory(string $type): bool
    {
        return isset($this->factories[$type]);
    }

    /**
     * addFactory
     *
     * @param  string    $type
     * @param  callable  $factory
     *
     * @return  $this
     */
    public function addFactory(string $type, callable|string $factory): static
    {
        if (is_string($factory)) {
            $factory = fn() => new $factory();
        }

        $this->factories[$type] = $factory;

        return $this;
    }

    public function addFilterCallback(string $type, \Closure $callback): static
    {
        return $this->addFactory($type, fn () => new CallbackFilter($callback));
    }

    /**
     * removeFactory
     *
     * @param  string  $type
     *
     * @return  $this
     */
    public function removeFactory(string $type): static
    {
        unset($this->factories[$type]);

        return $this;
    }

    /**
     * @return callable[]
     */
    public function getFactories(): array
    {
        return $this->factories;
    }

    /**
     * @param  callable[]  $factories
     *
     * @return  static  Return self to support chaining.
     */
    public function setFactories(array $factories): static
    {
        $this->factories = $factories;

        return $this;
    }

    protected function prepareDefaultFactories(): void
    {
        $this->addFactory('abs', Absolute::class);
        $this->addFactory('alnum', Alnum::class);
        $this->addFactory('cmd', Cmd::class);
        $this->addFactory('email', EmailAddress::class);
        $this->addFactory('url', UrlAddress::class);
        $this->addFactory('words', Words::class);
        $this->addFactory('ip', IPAddress::class);
        $this->addFactory('ipv4', IPV4::class);
        $this->addFactory('ipv6', IPV6::class);
        $this->addFactory('neg', Negative::class);
        $this->addFactory('raw', RawValue::class);
        $this->addFactory(
            'range',
            fn(array $options) => new Range(
                TypeCast::tryNumeric($options['min'] ?? null, true),
                TypeCast::tryNumeric($options['max'] ?? null, true)
            )
        );
        $this->addFactory(
            'clamp',
            fn(array $options) => new Clamp(
                TypeCast::tryNumeric($options['min'] ?? null, true),
                TypeCast::tryNumeric($options['max'] ?? null, true)
            )
        );
        $this->addFactory(
            'length',
            fn(array $options) => new Length(
                TypeCast::tryInteger($options['max'] ?? null, true),
                $options['utf8'] ?? true
            )
        );
        $this->addFactory(
            'regex',
            fn(array $options) => new Regex(
                $options['regex'] ?? '',
                $options['type'] ?? Regex::TYPE_MATCH,
            )
        );
        $this->addFactory('required', Required::class);
        $this->addFactory('default', fn(array $options) => new DefaultValue(array_key_first($options)));
        $this->addFactory('func', fn(array $options) => new CallbackFilter(array_key_first($options)));

        // types
        $types = [
            'string',
            'int',
            'float',
            'array',
            'bool',
            'object',
        ];

        foreach ($types as $type) {
            $this->addFactory($type, fn(array $options) => new CastTo($type, (bool) ($options['strict'] ?? false)));
        }
    }
}
