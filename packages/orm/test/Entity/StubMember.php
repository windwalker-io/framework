<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use DateTimeImmutable;
use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\EntitySetup;
use Windwalker\ORM\Attributes\ManyToMany;
use Windwalker\ORM\Attributes\MapBy;
use Windwalker\ORM\Attributes\MapMorphBy;
use Windwalker\ORM\Attributes\Mapping;
use Windwalker\ORM\Attributes\MorphBy;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Attributes\TargetTo;
use Windwalker\ORM\Cast\DateTimeCast;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Relation\RelationCollection;

/**
 * The StubMember class.
 */
#[Table('members')]
#[\AllowDynamicProperties]
class StubMember implements EntityInterface
{
    use EntityTrait;

    #[Column('id'), PK, AutoIncrement]
    protected ?int $id = null;

    #[Column('no')]
    protected string $no = '';

    #[Column('name')]
    protected string $name = '';

    #[Column('email')]
    protected string $email = '';

    #[Column('password')]
    protected string $password = '';

    #[Column('avatar')]
    protected string $avatar = '';

    #[Column('registered')]
    #[Cast(DateTimeCast::class)]
    protected ?DateTimeImmutable $registered = null;

    protected ?StubLicense $studentLicense = null;

    protected ?StubLicense $teacherLicense = null;

    #[ManyToMany]
    #[MapBy(StubMemberActionMap::class, 'no', 'member_no'), MapMorphBy(type: 'student')]
    #[TargetTo(StubAction::class, 'action_no', 'no'), MorphBy(type: 'member')]
    protected ?RelationCollection $actions = null;

    #[Mapping('member_action_map')]
    #[Cast(StubMemberActionMap::class, options: Cast::USE_HYDRATOR)]
    protected ?StubMemberActionMap $map = null;

    #[EntitySetup]
    public static function setup(
        EntityMetadata $metadata
    ): void {
        $rm = $metadata->getRelationManager();

        $rm->oneToOne('studentLicense')
            ->targetTo(StubLicense::class, 'no', 'target_no')
            ->morphBy(type: 'student');

        $rm->oneToOne('teacherLicense')
            ->targetTo(StubLicense::class, 'no', 'target_no')
            ->morphBy(type: 'teacher');
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     *
     * @return  static  Return self to support chaining.
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param  string  $email
     *
     * @return  static  Return self to support chaining.
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param  string  $password
     *
     * @return  static  Return self to support chaining.
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * @param  string  $avatar
     *
     * @return  static  Return self to support chaining.
     */
    public function setAvatar(string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getRegistered(): ?DateTimeImmutable
    {
        return $this->registered;
    }

    /**
     * @param  DateTimeImmutable|null  $registered
     *
     * @return  static  Return self to support chaining.
     */
    public function setRegistered(?DateTimeImmutable $registered): static
    {
        $this->registered = $registered;

        return $this;
    }

    /**
     * @return StubLicense|null
     */
    public function getStudentLicense(): ?StubLicense
    {
        return $this->loadRelation('studentLicense');
    }

    /**
     * @param  StubLicense|null  $studentLicense
     *
     * @return  static  Return self to support chaining.
     */
    public function setStudentLicense(?StubLicense $studentLicense): static
    {
        $this->studentLicense = $studentLicense;

        return $this;
    }

    /**
     * @return StubLicense|null
     */
    public function getTeacherLicense(): ?StubLicense
    {
        return $this->loadRelation('teacherLicense');
    }

    /**
     * @param  StubLicense|null  $teacherLicense
     *
     * @return  static  Return self to support chaining.
     */
    public function setTeacherLicense(?StubLicense $teacherLicense): static
    {
        $this->teacherLicense = $teacherLicense;

        return $this;
    }

    /**
     * @return RelationCollection|null
     */
    public function getActions(): RelationCollection
    {
        return $this->loadCollection('actions');
    }

    /**
     * @param  RelationCollection|null  $actions
     *
     * @return  static  Return self to support chaining.
     */
    public function setActions(?RelationCollection $actions): static
    {
        $this->actions = $actions;

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
}
