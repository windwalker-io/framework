<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Renderer\Provider;

use Windwalker\Core\Application\AppContext;
use Windwalker\Core\DI\RequestBootableProviderInterface;
use Windwalker\Core\Events\Web\AfterRoutingEvent;
use Windwalker\Core\Html\HtmlFrame;
use Windwalker\Core\Pagination\PaginationFactory;
use Windwalker\Core\Renderer\LayoutPathResolver;
use Windwalker\Core\Renderer\RendererService;
use Windwalker\Core\Router\Router;
use Windwalker\DI\BootableDeferredProviderInterface;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;
use Windwalker\Renderer\CompositeRenderer;
use Windwalker\Renderer\RendererInterface;

/**
 * The ViewProvider class.
 */
class RendererProvider implements ServiceProviderInterface, RequestBootableProviderInterface
{
    /**
     * @inheritDoc
     */
    public function bootBeforeRequest(Container $container): void
    {
        if ($container->has(AppContext::class)) {
            $app = $container->get(AppContext::class);

            $app->on(
                AfterRoutingEvent::class,
                function (AfterRoutingEvent $event) use ($container, $app) {
                    $route = $event->getMatched();

                    if ($route && $ns = $route->getExtraValue('namespace')) {
                        $container->get(RendererService::class)
                            ->addPath(WINDWALKER_VIEWS . '/' . $ns);
                    }
                }
            );
        }
    }

    /**
     * Registers the service provider with a DI container.
     *
     * @param  Container  $container  The DI container.
     *
     * @return  void
     * @throws \Windwalker\DI\Exception\DefinitionException
     */
    public function register(Container $container): void
    {
        $container->prepareObject(
            CompositeRenderer::class,
            function (CompositeRenderer $renderer) use ($container) {
                // $renderer->setPaths($container->getParam('renderer.paths') ?? []);
                $renderer->setFactories($container->getParam('renderer.renderers') ?? []);
                $renderer->setOptions($container->getParam('renderer.options') ?? []);

                return $renderer;
            },
            Container::ISOLATION
        )
            ->alias(RendererInterface::class, CompositeRenderer::class);

        $container->prepareSharedObject(LayoutPathResolver::class, null, Container::ISOLATION);

        $container->share(
            RendererService::class,
            function (Container $container) {
                return $container->newInstance(
                    RendererService::class,
                    [
                        'aliases' => $container->getParam('renderer.aliases'),
                    ]
                );
            },
            Container::ISOLATION
        );

        $container->prepareSharedObject(HtmlFrame::class, null, Container::ISOLATION);

        $this->registerPagination($container);
    }

    /**
     * registerPagination
     *
     * @param  Container  $container
     *
     * @return  void
     *
     * @throws \Windwalker\DI\Exception\DefinitionException
     */
    protected function registerPagination(Container $container): void
    {
        $container->prepareSharedObject(
            PaginationFactory::class,
            function (PaginationFactory $paginationFactory, Container $container) {
                $paginationFactory->setDefaultTemplate(
                    $container->getParam('renderer.pagination.template'),
                );
                $paginationFactory->setDefaultNeighbours(
                    $container->getParam('renderer.pagination.neighbours') ?? 4,
                );

                return $paginationFactory;
            },
            Container::ISOLATION
        );
    }
}
