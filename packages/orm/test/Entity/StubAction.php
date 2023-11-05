<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\EntitySetup;
use Windwalker\ORM\Attributes\Mapping;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Relation\RelationCollection;

/**
 * The StubAction class.
 */
#[Table('actions')]
#[\AllowDynamicProperties]
class StubAction implements EntityInterface
{
    use EntityTrait;

    #[Column('id'), PK, AutoIncrement]
    protected ?int $id = null;

    #[Column('no')]
    protected string $no = '';

    #[Column('title')]
    protected string $title = '';

    #[Column('type')]
    protected string $type = '';

    protected ?RelationCollection $members = null;

    #[Mapping('member_action_map')]
    #[Cast(StubMemberActionMap::class)]
    protected ?StubMemberActionMap $map = null;

    #[EntitySetup]
    public static function setup(
        EntityMetadata $metadata
    ) {
        $rm = $metadata->getRelationManager();

        $rm->manyToMany('members')
            ->mapBy(StubMemberActionMap::class, 'no', 'action_no')
            ->mapMorphBy(type: 'student')
            ->targetTo(StubMember::class, 'member_no', 'no');
    }

    /**
     * @return string
     */
    public function getNo(): string
    {
        return $this->no;
    }

    /**
     * @param  string  $no
     *
     * @return  static  Return self to support chaining.
     */
    public function setNo(string $no): static
    {
        $this->no = $no;

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
     * @return RelationCollection|null
     */
    public function getMembers(): ?RelationCollection
    {
        return $this->loadCollection('members');
    }

    /**
     * @param  RelationCollection|null  $members
     *
     * @return  static  Return self to support chaining.
     */
    public function setMembers(?RelationCollection $members): static
    {
        $this->members = $members;

        return $this;
    }

    /**
     * @return StubMemberActionMap|null
     */
    public function getMap(): ?StubMemberActionMap
    {
        return $this->map;
    }

    /**
     * @param  StubMemberActionMap|null  $map
     *
     * @return  static  Return self to support chaining.
     */
    public function setMap(?StubMemberActionMap $map): static
    {
        $this->map = $map;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param  string  $type
     *
     * @return  static  Return self to support chaining.
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
