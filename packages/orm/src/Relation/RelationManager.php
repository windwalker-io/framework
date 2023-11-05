<?php

declare(strict_types=1);

namespace Windwalker\ORM\Relation;

use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Relation\Strategy\AbstractRelation;
use Windwalker\ORM\Relation\Strategy\ManyToMany;
use Windwalker\ORM\Relation\Strategy\ManyToOne;
use Windwalker\ORM\Relation\Strategy\OneToMany;
use Windwalker\ORM\Relation\Strategy\OneToOne;
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;
use Windwalker\ORM\Relation\Strategy\RelationStrategyInterface;

/**
 * The Relation class.
 */
class RelationManager implements RelationStrategyInterface
{
    /**
     * @var AbstractRelation[]
     */
    protected array $relations = [];

    /**
     * RelationManager constructor.
     *
     * @param  EntityMetadata  $metadata
     */
    public function __construct(protected EntityMetadata $metadata)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function load(array $data, object $entity): array
    {
        foreach ($this->getRelations() as $relation) {
            $data = $relation->load($data, $entity);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function save(array $data, object $entity, ?array $oldData = null): void
    {
        foreach ($this->getRelations() as $relation) {
            $relation->save($data, $entity, $oldData);
        }
    }

    /**
     * @inheritDoc
     */
    public function beforeUpdate(array $data, object $entity, ?array $oldData = null): void
    {
        foreach ($this->getRelations() as $relation) {
            $relation->beforeUpdate($data, $entity, $oldData);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(array $data, object $entity): void
    {
        foreach ($this->getRelations() as $relation) {
            $relation->delete($data, $entity);
        }
    }

    /**
     * @inheritDoc
     */
    public function beforeDelete(array $data, object $entity): void
    {
        foreach ($this->getRelations() as $relation) {
            $relation->beforeDelete($data, $entity);
        }
    }

    public function oneToOne(
        string $field,
        ?string $targetTable = null,
        array|string $fks = [],
        string $onUpdate = Action::IGNORE,
        string $onDelete = Action::IGNORE,
        array $options = [],
    ): OneToOne {
        $rel = new OneToOne(
            $this->getMetadata(),
            $field,
            $targetTable,
            $fks,
            $onUpdate,
            $onDelete,
            $options
        );

        return $this->addRelation($field, $rel);
    }

    public function oneToMany(
        string $field,
        ?string $targetTable = null,
        array|string $fks = [],
        string $onUpdate = Action::IGNORE,
        string $onDelete = Action::IGNORE,
        array $options = [],
    ): OneToMany {
        $rel = new OneToMany(
            $this->getMetadata(),
            $field,
            $targetTable,
            $fks,
            $onUpdate,
            $onDelete,
            $options
        );

        return $this->addRelation($field, $rel);
    }

    public function manyToOne(
        string $field,
        ?string $targetTable = null,
        array|string $fks = [],
        string $onUpdate = Action::IGNORE,
        string $onDelete = Action::IGNORE,
        array $options = [],
    ): ManyToOne {
        $rel = new ManyToOne(
            $this->getMetadata(),
            $field,
            $targetTable,
            $fks,
            $onUpdate,
            $onDelete,
            $options
        );

        return $this->addRelation($field, $rel);
    }

    public function manyToMany(
        string $field,
        ?string $mapTable = null,
        array $mapFks = [],
        ?string $targetTable = null,
        array $fks = [],
        string $onUpdate = Action::IGNORE,
        string $onDelete = Action::IGNORE,
        array $options = []
    ): ManyToMany {
        $rel = new ManyToMany(
            $this->getMetadata(),
            $field,
            $mapTable,
            $mapFks,
            $targetTable,
            $fks,
            $onUpdate,
            $onDelete,
            $options
        );

        return $this->addRelation($field, $rel);
    }

    public function addRelation(string $propName, RelationConfigureInterface $relation): RelationConfigureInterface
    {
        $relation->setMetadata($this->getMetadata())->setPropName($propName);

        return $this->relations[$propName] = $relation;
    }

    /**
     * @return EntityMetadata
     */
    public function getMetadata(): EntityMetadata
    {
        return $this->metadata;
    }

    /**
     * @return AbstractRelation[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    public function getRelation(string $propName): ?AbstractRelation
    {
        return $this->relations[$propName] ?? null;
    }
}
