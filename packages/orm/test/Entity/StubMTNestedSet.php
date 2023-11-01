<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\NestedSet;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Nested\MultiTreeNestedEntityInterface;
use Windwalker\ORM\Nested\MultiTreeNestedEntityTrait;
use Windwalker\ORM\Nested\NestedPathableInterface;
use Windwalker\ORM\Nested\NestedPathableTrait;

/**
 * The MultiTreeNestedSet class.
 */
#[NestedSet('#__nestedsets')]
#[\AllowDynamicProperties]
class StubMTNestedSet implements NestedPathableInterface, MultiTreeNestedEntityInterface
{
    use NestedPathableTrait;
    use MultiTreeNestedEntityTrait;

    #[Column('id'), PK, AutoIncrement]
    protected ?int $id = null;

    #[Column('title')]
    protected string $title = '';

    #[Column('access')]
    protected int $access = 0;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param  int|null  $id
     *
     * @return  static  Return self to support chaining.
     */
    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param  string  $title
     *
     * @return  static  Return self to support chaining.
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return int
     */
    public function getAccess(): int
    {
        return $this->access;
    }

    /**
     * @param  int  $access
     *
     * @return  static  Return self to support chaining.
     */
    public function setAccess(int $access): static
    {
        $this->access = $access;

        return $this;
    }

    public function getPrimaryKeyValue(): int
    {
        return $this->getId();
    }
}
