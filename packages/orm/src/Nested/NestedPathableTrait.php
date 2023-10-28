<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Nested;

use Lyrasoft\Luna\Attributes\Slugify;
use Windwalker\ORM\Attributes\Column;

/**
 * The NestedPathableTrait class.
 */
trait NestedPathableTrait
{
    use NestedEntityTrait;

    #[Column('alias')]
    #[Slugify]
    protected string $alias = '';

    #[Column('path')]
    protected string $path = '';

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param  string  $alias
     *
     * @return  static  Return self to support chaining.
     */
    public function setAlias(string $alias): static
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param  string  $path
     *
     * @return  static  Return self to support chaining.
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }
}
