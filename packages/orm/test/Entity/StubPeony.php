<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;

/**
 * The StubPeony class.
 */
#[Table('peonies')]
class StubPeony implements EntityInterface
{
    use EntityTrait;

    #[Column('id')]
    protected int $id = 0;

    #[Column('no')]
    protected string $no = '';

    #[Column('location_id')]
    protected int $locationId = 0;

    #[Column('title')]
    protected string $title = '';

    #[Column('state')]
    protected int $state = 0;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return  static  Return self to support chaining.
     */
    public function setId(int $id): static
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
     * @param string $title
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
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     *
     * @return  static  Return self to support chaining.
     */
    public function setState(int $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string
     */
    public function getNo(): string
    {
        return $this->no;
    }

    /**
     * @param string $no
     *
     * @return  static  Return self to support chaining.
     */
    public function setNo(string $no): static
    {
        $this->no = $no;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocationId(): int
    {
        return $this->locationId;
    }

    /**
     * @param int $locationId
     *
     * @return  static  Return self to support chaining.
     */
    public function setLocationId(int $locationId): static
    {
        $this->locationId = $locationId;

        return $this;
    }
}
