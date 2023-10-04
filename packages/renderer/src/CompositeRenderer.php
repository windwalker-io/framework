<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Renderer;

use Closure;
use SplPriorityQueue;
use Windwalker\Edge\Exception\LayoutNotFoundException;
use Windwalker\Filesystem\Path;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Assert\LogicAssert;
use Windwalker\Utilities\Cache\InstanceCacheTrait;
use Windwalker\Utilities\Classes\ObjectBuilderAwareTrait;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\Paths\PathsAwareTrait;
use Windwalker\Utilities\Str;

/**
 * Class AbstractRenderer
 *
 * @since 2.0
 */
class CompositeRenderer implements RendererInterface, TemplateFactoryInterface, ExtendableRendererInterface
{
    use ObjectBuilderAwareTrait;
    use InstanceCacheTrait;
    use OptionAccessTrait;
    use PathsAwareTrait;

    protected array $factories = [
        'edge' => [
            EdgeRenderer::class,
            ['edge.php'],
        ],
        'blade' => [
            BladeRenderer::class,
            ['blade.php'],
        ],
        'plates' => [
            PlatesRenderer::class,
            ['php'],
        ],
        'mustache' => [
            MustacheRenderer::class,
            ['mustache'],
        ],
        'twig' => [
            TwigRenderer::class,
            ['twig'],
        ],
    ];

    protected array $aliases = [];

    /**
     * Class init.
     *
     * @param  string|array|SplPriorityQueue  $paths
     * @param  array                          $options
     */
    public function __construct(SplPriorityQueue|string|array $paths = [], array $options = [])
    {
        $this->setPaths($paths);

        $this->prepareOptions(
            [],
            $options
        );
    }

    /**
     * @inheritDoc
     */
    public function make(string $layout, array $options = []): Closure
    {
        $options = Arr::mergeRecursive($this->getOptions(), $options);

        $baseDir = null;

        if (is_file($layout)) {
            $baseDir = dirname($layout);
            $filename = Path::getFilename($layout);
        } else {
            $file = $this->findFile($layout);

            if (!$file) {
                throw new LayoutNotFoundException(
                    sprintf(
                        'Layout: %s not found in paths: %s',
                        $layout,
                        implode("\n| ", $this->dumpPaths())
                    )
                );
            }

            $pathname = $file->getPathname();
            $filename = $file->getFilename();
        }

        [$type, , $extension] = $this->matchExtension($filename);
        $renderer = $this->getRenderer($type);

        if ($renderer instanceof LayoutConverterInterface) {
            $layout = $renderer->handleLayout($layout, $extension);
        }

        // If found a file, we must use layout path to find relative base dir.
        if ($baseDir === null && isset($pathname)) {
            $baseDir = Str::removeRight($pathname, $layout);
        }

        $options['paths'] = $this->dumpPaths();
        $options['base_dir'] = $baseDir;

        return $renderer->make($layout, $options);
    }

    /**
     * @inheritDoc
     */
    public function render(string $layout, array $data = [], array $options = []): string
    {
        return $this->make($layout, $options)($data);
    }

    protected function matchExtension(string $layout): array
    {
        foreach ($this->getFactories() as $type => [$factory, $extensions]) {
            foreach ($extensions as $extension) {
                if (str_ends_with($layout, '.' . $extension)) {
                    return [$type, $factory, $extension];
                }
            }
        }

        throw new LayoutNotFoundException('There\'s no matched layout file found.');
    }

    /**
     * finFile
     *
     * @param  string  $layout
     *
     * @return \SplFileInfo|null
     */
    public function findFile(string $layout): ?\SplFileInfo
    {
        $layout = $this->resolveAlias($layout);

        $paths = clone $this->getPaths();

        $slashedLayout = str_replace('.', '/', $layout);

        foreach ($paths as $path) {
            // Match full name
            $filePath = $path . '/' . $layout;

            if (is_file($filePath)) {
                return new \SplFileInfo($filePath);
            }

            if ($info = static::findFileInfoByExtensions($path, $slashedLayout, $this->getSupportedExtensions())) {
                return $info;
            }
        }

        return null;
    }

    public static function findFileInfoByExtensions(string $base, string $filepath, array $extensions): ?\SplFileInfo
    {
        foreach ($extensions as $ext) {
            $filePath = $base . '/' . $filepath . '.' . $ext;

            if (is_file($filePath)) {
                return new \SplFileInfo($filePath);
            }
        }

        return null;
    }

    /**
     * has
     *
     * @param  string  $layout
     *
     * @return  bool
     *
     * @since  3.5.2
     */
    public function has(string $layout): bool
    {
        return $this->findFile($layout) !== null;
    }

    public function getRenderer(string $type, array $options = []): TemplateFactoryInterface
    {
        return $this->once(
            'renderer.' . $type,
            function () use ($options, $type) {
                [$factory, $exts] = $this->getFactory($type);

                return $this->getObjectBuilder()->createObject($factory, $options);
            }
        );
    }

    public function addFactory(string $type, string $class, array $extensions = []): void
    {
        LogicAssert::assert(
            is_subclass_of($class, TemplateFactoryInterface::class),
            sprintf(
                'Renderer factory should be sub class of %s, %s given.',
                TemplateFactoryInterface::class,
                $class
            )
        );

        $this->factories[$type] = [$class, $extensions];
    }

    public function addFileExtensions(string $type, array|string $extensions): void
    {
        LogicAssert::assert(
            (bool) $this->getFactory($type),
            sprintf(
                'Factory %s not registered.',
                $type
            )
        );

        $extensions = (array) $extensions;

        $this->factories[$type][1] = array_unique(
            array_merge($this->factories[$type][1], $extensions)
        );
    }

    public function removeFactory(string $type): void
    {
        unset($this->factories[$type]);
    }

    public function getFactory(string $type): ?array
    {
        return $this->factories[$type] ?? null;
    }

    public function setFactories(array $factories): void
    {
        $this->factories = [];

        foreach ($factories as $type => [$factory, $extensions]) {
            $this->addFactory($type, $factory, $extensions);
        }
    }

    public function getFactories(): array
    {
        return $this->factories;
    }

    /**
     * getSupportedExtensions
     *
     * @return  array
     */
    public function getSupportedExtensions(): array
    {
        return array_values(Arr::flatten(array_map(fn($factory) => $factory[1], $this->getFactories())));
    }

    /**
     * getFoundFileInfo
     *
     * @param  string  $filePath
     * @param  string  $path
     *
     * @return  array
     */
    protected static function getFoundFileInfo(string $filePath, string $path): array
    {
        $filename = Str::removeLeft(
            $filePath = Path::normalize($filePath),
            $path = Path::normalize($path),
        );

        return [
            $filePath,
            $path,
            ltrim($filename, '/\\'),
        ];
    }

    /**
     * alias
     *
     * @param  string  $alias
     * @param  string  $layout
     *
     * @return  static
     */
    public function alias(string $alias, string $layout): static
    {
        $this->aliases[$alias] = $layout;

        return $this;
    }

    public function resolveAlias(string $layout): string
    {
        while (isset($this->aliases[$layout])) {
            $layout = $this->aliases[$layout];
        }

        return $layout;
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @param  array  $aliases
     *
     * @return  static  Return self to support chaining.
     */
    public function setAliases(array $aliases): static
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * Extends engine after created, this is similar a decorator.
     *
     * @param  callable  $callable
     *
     * @return  static  Retrun self to support chaining.
     */
    public function extend(callable $callable): static
    {
        $builder = $this->getObjectBuilder();

        $handler = $builder->getBuilder();

        $handler = static function (string $className, array $options) use ($callable, $handler) {
            return $callable($handler($className, $options), $options);
        };

        $builder->setBuilder($handler);

        return $this;
    }
}
