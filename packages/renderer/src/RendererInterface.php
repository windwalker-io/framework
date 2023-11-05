<?php

declare(strict_types=1);

namespace Windwalker\Renderer;

/**
 * Interface RendererInterface
 */
interface RendererInterface
{
    /**
     * render
     *
     * @param  string  $layout
     * @param  array   $data
     * @param  array   $options
     *
     * @return  string
     */
    public function render(string $layout, array $data = [], array $options = []): string;
}
