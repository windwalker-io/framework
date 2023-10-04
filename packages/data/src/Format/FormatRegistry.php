<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Data\Format;

use Closure;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Traversable;
use Windwalker\Utilities\Contract\DumpableInterface;
use Windwalker\Utilities\Reflection\ReflectAccessor;

use function strlen;

/**
 * The FormatRegistry class.
 *
 * @since  __DEPLOY_VERSION__
 */
class FormatRegistry
{
    /**
     * @var  array
     */
    protected array $handlers = [];

    /**
     * @var  array
     */
    protected array $aliases = [];

    /**
     * @var  array
     */
    protected array $extMaps = [];

    /**
     * @var  string
     */
    public string $defaultFormat = 'json';

    /**
     * FormatRegistry constructor.
     *
     * @param  array  $handlers
     */
    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;

        $this->alias('yml', 'yaml');
        $this->extMap('yml', 'yaml');
    }

    /**
     * parse
     *
     * @param  string  $string
     * @param  string  $format
     * @param  array   $options
     *
     * @return array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function parse(string $string, ?string $format = null, array $options = []): mixed
    {
        $format ??= $this->defaultFormat;

        return $this->getFormatHandler($this->resolveFormatAlias($format))
            ->parse($string, $options);
    }

    public function dump(mixed $data, ?string $format = null, array $options = []): string
    {
        $format ??= $this->defaultFormat;

        return $this->getFormatHandler($this->resolveFormatAlias($format))
            ->dump($data, $options);
    }

    public function loadFile(string|\SplFileInfo $file, ?string $format = null, array $options = []): mixed
    {
        if ($file instanceof \SplFileInfo) {
            $file = $file->getPathname();
        }

        if ($format === null) {
            $paths = explode('.', $file);
            $ext = $paths[array_key_last($paths)];

            $format = $this->resolveFileFormat($ext);
        }

        if ($format === 'php') {
            return require $file;
        }

        return $this->parse(file_get_contents($file), $format ?? $this->defaultFormat, $options);
    }

    public function load(string|StreamInterface $string, ?string $format = null, array $options = [])
    {
        if ($string instanceof StreamInterface) {
            $string = $string->getContents();
        }

        if (strlen($string) < PHP_MAXPATHLEN && is_file($string)) {
            return $this->loadFile($string, $format, $options);
        }

        return $this->parse($string, $format ?: $this->defaultFormat, $options);
    }

    public function getFormatHandler(string $format): FormatInterface
    {
        return $this->handlers[$format] ?? static::createFormatHandler($format);
    }

    /**
     * registerFormat
     *
     * @param  string                    $format
     * @param  FormatInterface|callable  $handlerOrParser
     * @param  callable|null             $dumper
     *
     * @return  $this
     *
     * @since  __DEPLOY_VERSION__
     */
    public function registerFormat(
        string $format,
        callable|FormatInterface $handlerOrParser,
        ?callable $dumper = null
    ): static {
        if ($handlerOrParser instanceof FormatInterface) {
            $this->handlers[strtolower($format)] = $handlerOrParser;

            return $this;
        }

        if (!is_callable($handlerOrParser)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Format handler should be %s or callable',
                    FormatInterface::class
                )
            );
        }

        if (!is_callable($dumper)) {
            throw new InvalidArgumentException('Dumper should be callable callable');
        }

        $this->handlers[$format] = new CallbackFormatHandler($handlerOrParser, $dumper);

        return $this;
    }

    public static function createFormatHandler(string $format): FormatInterface
    {
        $class = sprintf(
            'Windwalker\Data\Format\%sFormat',
            ucfirst($format)
        );

        return new $class();
    }

    public function removeHandler(string $formst): static
    {
        unset($this->handlers[strtolower($formst)]);

        return $this;
    }

    public function resolveFormatAlias(string $format): string
    {
        return $this->aliases[strtolower($format)] ?? $format;
    }

    public function resolveFileFormat(string $ext): string
    {
        return $this->extMaps[strtolower($ext)] ?? $ext;
    }

    public function alias(string $alias, string $format): static
    {
        $this->aliases[strtolower($alias)] = strtolower($format);

        return $this;
    }

    public function extMap(string $ext, string $format): static
    {
        $this->extMaps[strtolower($ext)] = strtolower($format);

        return $this;
    }

    public function clearAlias(): FormatRegistry
    {
        $this->aliases = [];

        return $this;
    }

    public function clearExtMap(): FormatRegistry
    {
        $this->extMaps = [];

        return $this;
    }

    /**
     * makeDumpable
     *
     * @param  mixed  $data
     *
     * @return  array
     */
    public static function makeDumpable(mixed $data): array
    {
        // Ensure the input data is an array.
        if ($data instanceof DumpableInterface) {
            $data = $data->dump(true);
        } elseif ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        } elseif (is_object($data)) {
            $data = ReflectAccessor::getPropertiesValues($data);
        } else {
            $data = (array) $data;
        }

        $data = array_map(
            static function ($v) {
                if (is_resource($v)) {
                    return '[resource #' . get_resource_id($v) . ']';
                }

                if ($v instanceof Closure) {
                    return "[Object Closure]";
                }

                return $v;
            },
            $data,
        );

        foreach ($data as &$value) {
            if (is_array($value) || is_object($value)) {
                $value = static::makeDumpable($value);
            }
        }

        return $data;
    }
}
