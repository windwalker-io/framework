<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Renderer;

use Closure;
use Windwalker\Edge\Cache\EdgeArrayCache;
use Windwalker\Edge\Cache\EdgeFileCache;
use Windwalker\Edge\Compiler\EdgeCompiler;
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeFileLoader;

/**
 * The EdgeRenderer class.
 *
 * @since  3.0
 */
class EdgeRenderer extends AbstractEngineRenderer
{
    /**
     * @inheritDoc
     */
    public function make(string $layout, array $options = []): Closure
    {
        /** @var Edge $engine */
        $engine = $this->createEngine($options);

        return fn(array $data = []) => $engine->renderWithContext($layout, $data, $options['context'] ?? null);
    }

    /**
     * Get default builder function.
     *
     * @return  Closure
     */
    public function getDefaultEngineBuilder(): Closure
    {
        return static function (array $options = []) {
            if ($options['cache_path'] ?? null) {
                $cache = new EdgeFileCache($options['cache_path']);
            } else {
                $cache = new EdgeArrayCache();
            }

            return new Edge(
                new EdgeFileLoader(
                    $options['paths'] ?? [],
                    $options['file_extensions'] ?: null
                ),
                $cache,
                new EdgeCompiler(),
            );
        };
    }
}
