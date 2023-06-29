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
use Twig\Environment as Twig;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

/**
 * Class PhpRenderer
 *
 * @since 2.0
 */
class TwigRenderer extends AbstractEngineRenderer implements LayoutConverterInterface
{
    /**
     * Get default builder function.
     *
     * @return  Closure
     */
    public function getDefaultEngineBuilder(): Closure
    {
        return static function (array $options = []) {
            $twig = new Twig(
                new FilesystemLoader(
                    $options['paths'] ?? [],
                    $options['root_path'] ?? null
                ),
                $options['twig'] ?? []
            );

            if ($options['debug'] ?? null) {
                $twig->addExtension(new DebugExtension());
            }

            return $twig;
        };
    }

    /**
     * @inheritDoc
     */
    public function make(string $layout, array $options = []): Closure
    {
        /** @var Twig $engine */
        $engine = $this->createEngine($options);

        return fn(array $data = []) => $engine->render($layout, $data);
    }

    public function handleLayout(string $layout, string $ext): string
    {
        if (str_ends_with($layout, $ext)) {
            return $layout;
        }

        return str_replace('.', '/', $layout) . '.' . $ext;
    }
}
