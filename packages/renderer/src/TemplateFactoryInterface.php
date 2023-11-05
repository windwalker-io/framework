<?php

declare(strict_types=1);

namespace Windwalker\Renderer;

use Closure;

/**
 * Interface RendererFactoryInterface
 */
interface TemplateFactoryInterface
{
    /**
     * Make template engine.
     *
     * @param  string  $layout
     * @param  array   $options
     *
     * @return  Closure
     */
    public function make(string $layout, array $options = []): Closure;
}
