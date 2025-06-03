<?php

declare(strict_types=1);

namespace Windwalker\ORM\Nested;

use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\EntityTrait;
use Windwalker\ORM\Relation\RelationCollection;

/**
 * The NestedEntityTrait class.
 */
trait NestedEntityTrait
{
    use EntityTrait;

    #[Column('parent_id')]
    public mixed $parentId = null;

    #[Column('lft')]
    public int $lft = 0;

    #[Column('rgt')]
    public int $rgt = 0;

    #[Column('level')]
    public int $level = 0;

    protected ?Position $position = null;

    protected ?RelationCollection $children = null;

    protected ?RelationCollection $ancestors = null;

    protected ?RelationCollection $tree = null;

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position ??= new Position();
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->getRgt() - $this->getLft() + 1;
    }

    public function childrenCount(): int
    {
        return ($this->getRgt() - $this->getLft() - 1) / 2;
    }

    /**
     * @return int
     *
     * @deprecated  Use property instead.
     */
    public function getLft(): int
    {
        return $this->lft;
    }

    /**
     * @param  int  $lft
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setLft(int $lft): static
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * @return int
     *
     * @deprecated  Use property instead.
     */
    public function getRgt(): int
    {
        return $this->rgt;
    }

    /**
     * @param  int  $rgt
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setRgt(int $rgt): static
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * @return int
     *
     * @deprecated  Use property instead.
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param  int  $level
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return mixed|null
     *
     * @deprecated  Use property instead.
     */
    public function getParentId(): mixed
    {
        return $this->parentId;
    }

    /**
     * @param  mixed|null  $parentId
     *
     * @return  static  Return self to support chaining.
     *
     * @deprecated  Use property instead.
     */
    public function setParentId(mixed $parentId): static
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function isLeaf(): bool
    {
        return \Windwalker\count($this->getChildren()) === 0;
    }

    public function isRoot(): bool
    {
        return $this->level === 0;
    }

    public function getChildren(): RelationCollection
    {
        return $this->loadCollection('children');
    }

    /**
     * @param  RelationCollection  $children
     *
     * @return  static  Return self to support chaining.
     */
    public function setChildren(RelationCollection $children): static
    {
        $this->children = $children;

        return $this;
    }

    public function getAncestors(): RelationCollection
    {
        return $this->loadCollection('ancestors');
    }

    /**
     * @param  RelationCollection  $ancestors
     *
     * @return  static  Return self to support chaining.
     */
    public function setAncestors(RelationCollection $ancestors): static
    {
        $this->ancestors = $ancestors;

        return $this;
    }

    /**
     * @return RelationCollection
     */
    public function getTree(): RelationCollection
    {
        return $this->loadCollection('tree');
    }

    /**
     * @param  RelationCollection  $tree
     *
     * @return  static  Return self to support chaining.
     */
    public function setTree(RelationCollection $tree): static
    {
        $this->tree = $tree;

        return $this;
    }
}
