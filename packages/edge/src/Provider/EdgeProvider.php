<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Provider;

use Windwalker\Core\Edge\CoreFileLoader;
use Windwalker\Core\Renderer\Edge\WindwalkerExtension;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;
use Windwalker\Edge\Cache\EdgeFileCache;
use Windwalker\Edge\Component\ComponentExtension;
use Windwalker\Edge\Edge;
use Windwalker\Renderer\CompositeRenderer;
use Windwalker\Renderer\EdgeRenderer;
use Windwalker\Renderer\RendererInterface;

/**
 * The EdgeProvider class.
 */
class EdgeProvider implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param  Container  $container  The DI container.
     *
     * @return  void
     */
    public function register(Container $container): void
    {
        $container->extend(
            CompositeRenderer::class,
            function (CompositeRenderer $renderer, Container $container) {
                return $renderer->extend(
                    function (RendererInterface $renderer, array $options) use ($container) {
                        if ($renderer instanceof EdgeRenderer) {
                            $renderer = $this->doExtends($container, $renderer, $options);
                        }

                        return $renderer;
                    }
                );
            }
        );
    }

    protected function doExtends(
        Container $container,
        EdgeRenderer $renderer,
        array $options
    ): EdgeRenderer {
        return $renderer->extend(
            function (Edge $edge, array $options) use ($container) {
                $edge->getObjectBuilder()
                    ->setBuilder(
                        function (string $class, ...$args) use ($container) {
                            return $container->newInstance($class, $args);
                        }
                    );

                $this->prepareComponents($container, $edge, $options);

                $edge->addExtension(
                    $container->newInstance(WindwalkerExtension::class)
                );
                $edge->setLoader(
                    $container->newInstance(
                        CoreFileLoader::class,
                        [
                            'loader' => $edge->getLoader(),
                            'extensions' => $container->getParam('renderer.renderers.edge.1'),
                        ]
                    )
                );

                $cache = $edge->getCache();

                if ($cache instanceof EdgeFileCache) {
                    $cache->setDebug($container->getParam('app.debug'));
                }

                return $edge;
            }
        );
    }

    protected function prepareComponents(
        Container $container,
        Edge $edge,
        array $options
    ): Edge {
        $extension = $container->createSharedObject(ComponentExtension::class, ['edge' => $edge]);

        foreach ((array) $container->getParam('renderer.edge.components') as $name => $class) {
            $extension->registerComponent($name, $class);
        }

        return $edge->addExtension($extension);
    }
}
