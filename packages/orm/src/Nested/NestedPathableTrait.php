<?php

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
    public string $alias = '';

    #[Column('path')]
    public string $path = '';

    /**
     * @return string
     *
     * @deprecated  Use property instead.
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param  string  $alias
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setAlias(string $alias): static
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return string
     *
     * @deprecated  Use property instead.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param  string  $path
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }
}
