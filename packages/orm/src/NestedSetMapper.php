<?php

declare(strict_types=1);

namespace Windwalker\ORM;

use InvalidArgumentException;
use Windwalker\Data\Collection;
use Windwalker\Database\Event\HydrateEvent;
use Windwalker\Event\EventInterface;
use Windwalker\ORM\Event\AfterDeleteEvent;
use Windwalker\ORM\Event\BeforeDeleteEvent;
use Windwalker\ORM\Event\BeforeSaveEvent;
use Windwalker\ORM\Exception\NestedHandleException;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Nested\MultiTreeNestedEntityInterface;
use Windwalker\ORM\Nested\NestedEntityInterface;
use Windwalker\ORM\Nested\NestedPathableInterface;
use Windwalker\ORM\Nested\NestedPosition;
use Windwalker\ORM\Nested\Position;
use Windwalker\ORM\Relation\RelationCollection;
use Windwalker\ORM\Relation\RelationProxies;
use Windwalker\Query\Query;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Assert\ArgumentsAssert;
use Windwalker\Utilities\Cache\InstanceCacheTrait;

use function Windwalker\Query\qn;
use function Windwalker\raw;

/**
 * The NestedSetMapper class.
 *
 * @template T
 *
 * @implements EntityMapper<T>
 */
class NestedSetMapper extends EntityMapper
{
    use InstanceCacheTrait;

    protected const PARENT = 'parent';

    protected const LEFT = 'left';

    protected const RIGHT = 'right';

    protected int $depth = 0;

    protected function init(): void
    {
        parent::init();

        $this->on(
            BeforeSaveEvent::class,
            function (BeforeSaveEvent $event) {
                $source = $event->getSource();

                $isRoot = is_array($source) && ($source['is_root'] ?? null);

                if (!$isRoot) {
                    $this->preprocessSave($event, $event->getType() === BeforeSaveEvent::TYPE_CREATE);
                }

                $this->validateSave($event);
            }
        );

        $this->on(
            AfterDeleteEvent::class,
            function (AfterDeleteEvent $event) {
                $this->postProcessDelete($event);
            }
        );

        $this->on(
            HydrateEvent::class,
            function (HydrateEvent $event) {
                $this->postProcessFindHydration($event);
            }
        );
    }

    protected function postProcessFindHydration(HydrateEvent $event): void
    {
        $item = $event->getItem();

        if ($item === null) {
            return;
        }

        if (!$item instanceof NestedEntityInterface) {
            return;
        }

        $metadata = $this->getMetadata();
        $pk = $this->extractField($item, $this->getMainKey());

        // Children
        $getter = fn() => new RelationCollection(
            $this->getMetadata()->getClassName(),
            $this->select()
                ->where('parent_id', $pk)
                ->order('lft')
        );

        RelationProxies::set($item, $metadata->getOption('props')['children'], $getter);

        // Ancestors
        $key = $this->getMainKey();

        $getter = fn() => new RelationCollection(
            $this->getMetadata()->getClassName(),
            $this->getORM()
                ->select('p.*')
                ->from(
                    [
                        [$metadata->getClassName(), 'n'],
                        [$metadata->getClassName(), 'p'],
                    ]
                )
                ->where('n.lft', 'between', [qn('p.lft'), qn('p.rgt')])
                ->where('n.' . $key, '=', $pk)
                ->where('p.' . $key, '!=', $pk)
                ->tapIf(
                    $this->isMultiTree(),
                    fn (Query $query) => $query->where('n.root_id', $item->getRootId())
                )
                ->order('p.lft')
        );

        RelationProxies::set($item, $metadata->getOption('props')['ancestors'], $getter);

        // Tree
        $key = $this->getMainKey();

        $getter = fn() => new RelationCollection(
            $this->getMetadata()->getClassName(),
            $this->getORM()
                ->select('n.*')
                ->from(
                    [
                        [$metadata->getClassName(), 'n'],
                        [$metadata->getClassName(), 'p'],
                    ]
                )
                ->where('n.lft', 'between', [qn('p.lft'), qn('p.rgt')])
                ->where('p.' . $key, '=', $pk)
                ->tapIf(
                    $this->isMultiTree(),
                    fn (Query $query) => $query->where('n.root_id', $item->getRootId())
                )
                ->order('n.lft')
        );

        RelationProxies::set($item, $metadata->getOption('props')['tree'], $getter);
    }

    /**
     * getAncestors
     *
     * @param  string|int|object  $pkOrEntity
     *
     * @return  Collection|T[]
     */
    public function getPath(string|int|NestedEntityInterface $pkOrEntity): Collection
    {
        ArgumentsAssert::assert(
            is_object($pkOrEntity) || is_scalar($pkOrEntity),
            '{caller} conditions should be object or scalar, {value} given',
            $pkOrEntity
        );

        $metadata = $this->getMetadata();
        $key = $metadata->getMainKey();

        $pk = $this->entityToPk($pkOrEntity);

        return $this->getORM()
            ->select('p.*')
            ->from(
                [
                    [$metadata->getClassName(), 'n'],
                    [$metadata->getClassName(), 'p'],
                ]
            )
            ->where('n.lft', 'between', [qn('p.lft'), qn('p.rgt')])
            ->where('n.' . $key, '=', $pk)
            ->tapIf(
                $this->isMultiTree(),
                fn (Query $query) => $query->whereRaw('n.root_id = p.root_id')
            )
            ->order('p.lft')
            ->all($metadata->getClassName());
    }

    /**
     * getAncestors
     *
     * @param  string|int|NestedEntityInterface  $pkOrEntity
     *
     * @return  Collection|T[]
     */
    public function getAncestors(string|int|NestedEntityInterface $pkOrEntity): Collection
    {
        ArgumentsAssert::assert(
            is_object($pkOrEntity) || is_scalar($pkOrEntity),
            '{caller} conditions should be object or scalar, {value} given',
            $pkOrEntity
        );

        $metadata = $this->getMetadata();
        $key = $metadata->getMainKey();

        $pk = $this->entityToPk($pkOrEntity);

        return $this->getORM()
            ->select('p.*')
            ->from(
                [
                    [$metadata->getClassName(), 'n'],
                    [$metadata->getClassName(), 'p'],
                ]
            )
            ->where('n.lft', 'between', [qn('p.lft'), qn('p.rgt')])
            ->where('n.' . $key, '=', $pk)
            ->where('p.' . $key, '!=', $pk)
            ->tapIf(
                $this->isMultiTree(),
                fn (Query $query) => $query->whereRaw('n.root_id = p.root_id')
            )
            ->order('p.lft')
            ->all($metadata->getClassName());
    }

    /**
     * getTree
     *
     * @param  string|int|NestedEntityInterface  $pkOrEntity
     *
     * @return  Collection|T[]
     */
    public function getTree(string|int|NestedEntityInterface $pkOrEntity): Collection
    {
        ArgumentsAssert::assert(
            is_object($pkOrEntity) || is_scalar($pkOrEntity),
            '{caller} conditions should be object or scalar, {value} given',
            $pkOrEntity
        );

        $metadata = $this->getMetadata();
        $key = $metadata->getMainKey();

        $pk = $this->entityToPk($pkOrEntity);

        return $this->getORM()
            ->select('n.*')
            ->from(
                [
                    [$metadata->getClassName(), 'n'],
                    [$metadata->getClassName(), 'p'],
                ]
            )
            ->where('n.lft', 'between', [qn('p.lft'), qn('p.rgt')])
            ->where('p.' . $key, '=', $pk)
            ->tapIf(
                $this->isMultiTree(),
                fn (Query $query) => $query->whereRaw('n.root_id = p.root_id')
            )
            ->order('n.lft')
            ->all($metadata->getClassName());
    }

    public function isLeaf(string|int|NestedEntityInterface $pkOrEntity): bool
    {
        $metadata = $this->getMetadata();

        if (is_object($pkOrEntity) && $metadata::isEntity($pkOrEntity)) {
            $node = $this->extract($pkOrEntity);
        } else {
            $node = $this->findOne($pkOrEntity);

            if ($node === null) {
                return false;
            }
        }

        return ($node->getRgt() - $node->getLft()) === 1;
    }

    private function entityToPk(string|int|NestedEntityInterface $entity): mixed
    {
        if (is_object($entity) && EntityMetadata::isEntity($entity)) {
            return $this->extract($entity)[$this->getMainKey()];
        }

        return $entity;
    }

    public function setPosition(
        NestedEntityInterface $entity,
        mixed $referenceId,
        NestedPosition $position = NestedPosition::LAST_CHILD
    ): static {
        $referenceId = $this->entityToPk($referenceId);

        $entity->getPosition()
            ->setReferenceId($referenceId)
            ->setPosition($position);

        return $this;
    }

    public function setPositionAppendTo(NestedEntityInterface $entity, mixed $referenceId): static
    {
        return $this->setPosition($entity, $referenceId, Position::LAST_CHILD);
    }

    public function setPositionPrependTo(NestedEntityInterface $entity, mixed $referenceId): static
    {
        return $this->setPosition($entity, $referenceId, Position::FIRST_CHILD);
    }

    public function setPositionAfterOf(NestedEntityInterface $entity, mixed $referenceId): static
    {
        return $this->setPosition($entity, $referenceId, Position::AFTER);
    }

    public function setPositionBeforeOf(NestedEntityInterface $entity, mixed $referenceId): static
    {
        return $this->setPosition($entity, $referenceId, Position::BEFORE);
    }

    protected function preprocessSave(BeforeSaveEvent $event, bool $new = false): void
    {
        $data = $event->getData();
        /** @var NestedEntityInterface $entity */
        $entity = $this->toEntity($event->getSource());
        $position = $entity->getPosition();
        $className = $this->getMetadata()->getClassName();

        $k = $this->getMainKey();

        /*
         * If the primary key is empty, then we assume we are inserting a new node into the
         * tree.  From this point we would need to determine where in the tree to insert it.
         */
        if ($new) {
            /*
             * We are inserting a node somewhere in the tree with a known reference
             * node.  We have to make room for the new node and set the left and right
             * values before we insert the row.
             */
            if ($position->getReferenceId() < 0) {
                throw new NestedHandleException('ReferenceId is negative.');
            }

            // We are inserting a node relative to the last root node.
            if (!$position->getReferenceId()) {
                $reference = $this->select($k, 'parent_id', 'level', 'lft', 'rgt')
                    ->where('id', $entity->getParentId() ?: $this->getEmptyParentId())
                    ->order('lft', 'DESC')
                    ->limit(1)
                    ->get($className);
            } else {
                // We have a real node set as a location reference.
                // Get the reference node by primary key.
                /** @var NestedEntityInterface $reference */
                $reference = $this->findOne($position->getReferenceId(), $className);

                if (!$reference) {
                    throw new NestedHandleException(
                        sprintf('Reference ID %s not found.', $position->getReferenceId())
                    );
                }
            }

            // Get the reposition data for shifting the tree and re-inserting the node.
            [$newData, $leftWhere, $rightWhere, $baseWhere] = $this->getTreeRepositionData(
                $reference,
                2,
                $position->getPosition()
            );

            // Create space in the tree at the new location for the new node in left ids.
            $this->update()
                ->set('lft', raw('lft + 2'))
                ->where(...$leftWhere)
                ->where($baseWhere)
                ->execute();

            // Create space in the tree at the new location for the new node in right ids.
            $this->update()
                ->set('rgt', raw('rgt + 2'))
                ->where(...$rightWhere)
                ->where($baseWhere)
                ->execute();

            $data = array_merge($data, $newData);

            if ($this->isPathable()) {
                $data['path'] = $this->calculatePath($data);
            }

            $event->setData($data);
        } elseif ($position->getReferenceId()) {
            /*
             * If we have a given primary key then we assume we are simply updating this
             * node in the tree.  We should assess whether or not we are moving the node
             * or just updating its data fields.
             */

            $reference = $this->findOne($position->getReferenceId(), $className);

            // If the location has been set, move the node to its new location.
            $entity = $this->moveByReference(
                $data,
                $reference,
                $position->getPosition()
            );

            $data['lft'] = $entity->getLft();
            $data['rgt'] = $entity->getRgt();
            $data['level'] = $entity->getLevel();

            $event->setData($data);
        }
    }

    protected function validateSave(BeforeSaveEvent $event): void
    {
        $k = $this->getMainKey();
        $data = $event->getData();
        $oldData = $event->getOldData();

        if ($oldData) {
            $data = array_filter($data);
            $data = array_merge(
                $oldData,
                $data
            );
        }

        $pk = $data[$k] ?? null;
        $parentId = $data['parent_id'] ?? null;
        $source = $event->getSource();

        if (is_array($source) && ($source['is_root'] ?? null)) {
            $this->checkRootExists();
        } else {
            // Parent ID should not be NULL
            if (!$parentId) {
                throw new NestedHandleException(
                    sprintf(
                        'Invalid parent_id: %s',
                        $parentId
                    )
                );
            }

            // Parent ID should exists in DB.
            $parent = $this->select('id')
                ->where($this->getMainKey(), $parentId)
                ->result();

            if (!$parent) {
                throw new NestedHandleException(
                    sprintf(
                        'Parent ID: %s not exists.',
                        $parentId
                    )
                );
            }

            if ($pk) {
                // Parent should not be self
                if ($pk == $parentId) {
                    throw new NestedHandleException('Parent should not be self.');
                }

                $tree = $this->getTree($pk);

                // Parent should not be child.
                if ($tree->map(fn(object $entity) => $this->extract($entity)[$k])->contains($parentId)) {
                    throw new NestedHandleException('Parent should not be child.');
                }
            }
        }
    }

    /**
     * move
     *
     * @param  array|object  $source
     * @param  int           $delta
     * @param  mixed         $conditions
     *
     * @return  false|NestedEntityInterface|T
     * @throws \ReflectionException
     */
    public function move(array|object $source, int $delta, mixed $conditions = []): false|NestedEntityInterface
    {
        $node = $this->sourceToEntity($source);

        $query = $this->select()
            ->where('parent_id', $node->getParentId())
            ->where($this->conditionsToWheres($conditions));

        if ($delta > 0) {
            $query->where('rgt', '>', $node->getRgt())
                ->order('rgt', 'ASC');

            $position = Position::AFTER;
        } elseif ($delta < 0) {
            $query->where('lft', '<', $node->getLft())
                ->order('lft', 'DESC');

            $position = Position::BEFORE;
        } else {
            throw new InvalidArgumentException(
                __METHOD__ . '() argument #2 should not be 0.'
            );
        }

        $reference = $query->get($this->getMetadata()->getClassName());

        if ($reference) {
            return $this->moveByReference($node, $reference, $position);
        }

        return false;
    }

    /**
     * moveByReference
     *
     * @param  array|object    $source
     * @param  mixed           $reference
     * @param  NestedPosition  $position
     *
     * @return NestedEntityInterface
     *
     * @throws \ReflectionException
     */
    public function moveByReference(
        mixed $source,
        mixed $reference,
        NestedPosition $position
    ): NestedEntityInterface {
        /** @var NestedEntityInterface|MultiTreeNestedEntityInterface $node */
        $node = $this->sourceToEntity($source);

        $k = $this->getMainKey();

        $className = $this->getMetadata()->getClassName();

        // We are moving the tree relative to a reference node.
        if ($reference) {
            if (!$reference instanceof NestedEntityInterface) {
                $reference = $this->mustFindOne($reference, $className);
            }
        } else {
            // We are moving the tree to be the last child of the root node
            // Get the last root node as the reference node.
            /** @var NestedEntityInterface $reference */
            $reference = $this->select($this->getMainKey(), 'parent_id', 'level', 'lft', 'rgt')
                ->where('parent_id', $this->getEmptyParentId())
                ->order('lft', 'DESC')
                ->limit(1)
                ->get($className);
        }

        /** @var NestedEntityInterface|MultiTreeNestedEntityInterface $reference */

        // Get the ids of child nodes.
        $children = $this->select($k)
            ->where('lft', 'between', [$node->getLft(), $node->getRgt()])
            ->tapIf(
                $this->isMultiTree(),
                fn (Query $query) => $query->where('root_id', $reference->getRootId())
            )
            ->loadColumn()
            ->dump();

        // Cannot move the node to be a child of itself.
        if (in_array($reference->getPrimaryKeyValue(), $children)) {
            throw new NestedHandleException(
                sprintf(
                    '%s::moveByReference(%d, %s) failed parenting to child.',
                    static::class,
                    $reference->getPrimaryKeyValue(),
                    $position->name
                )
            );
        }

        /*
         * Move the sub-tree out of the nested sets by negating its left and right values.
         */
        $this->update()
            ->set('lft', raw('lft * (-1)'))
            ->set('rgt', raw('rgt * (-1)'))
            ->tapIf(
                $this->isMultiTree(),
                fn (Query $query) => $query->set('root_id', $reference->getRootId())
                    ->where('root_id', $node->getRootId())
            )
            ->where('lft', 'between', [$node->getLft(), $node->getRgt()])
            ->execute();

        /*
         * Close the hole in the tree that was opened by removing the sub-tree from the nested sets.
         */

        // Compress the left values.
        $this->update()
            ->set('lft', raw('lft - ' . $node->getWidth()))
            ->where('lft', '>', $node->getRgt())
            ->tapIf(
                $this->isMultiTree(),
                fn (Query $query) => $query->where('root_id', $node->getRootId())
            )
            ->execute();

        // Compress the right values.
        $this->update()
            ->set('rgt', raw('rgt - ' . $node->getWidth()))
            ->where('rgt', '>', $node->getRgt())
            ->tapIf(
                $this->isMultiTree(),
                fn (Query $query) => $query->where('root_id', $node->getRootId())
            )
            ->execute();

        // Re-load reference node since it may be updated above.
        $reference = $this->mustFindOne($reference->getPrimaryKeyValue());

        // Get the reposition data for re-inserting the node after the found root.
        [$newData, $leftWhere, $rightWhere, $baseWhere] = $this->getTreeRepositionData(
            $reference,
            $node->getWidth(),
            $position
        );

        /*
         * Create space in the nested sets at the new location for the moved sub-tree.
         */

        // Shift left values.
        $this->update()
            ->set('lft', raw('lft + ' . $node->getWidth()))
            ->where(...$leftWhere)
            ->where($baseWhere)
            ->execute();

        // Shift right values.
        $this->update()
            ->set('rgt', raw('rgt + ' . $node->getWidth()))
            ->where(...$rightWhere)
            ->where($baseWhere)
            ->execute();

        /*
         * Calculate the offset between where the node used to be in the tree and
         * where it needs to be in the tree for left ids (also works for right ids).
         */

        $offset = $newData['lft'] - $node->getLft();
        $levelOffset = $newData['level'] - $node->getLevel();

        // Move the nodes back into position in the tree using the calculated offsets.
        $this->update()
            ->set('rgt', raw(((int) $offset) . ' - rgt'))
            ->set('lft', raw(((int) $offset) . ' - lft'))
            ->set('level', raw('level + ' . ((int) $levelOffset)))
            ->where('lft', '<', 0)
            ->where($baseWhere)
            ->execute();

        // Set the correct parent id for the moved node if required.
        if ($node->getParentId() != $newData['parent_id']) {
            $this->update()
                ->set('parent_id', $newData['parent_id'])
                ->where($k, $node->getPrimaryKeyValue())
                ->execute();
        }

        $node = $this->hydrate($newData, $node);

        return $node;
    }

    /**
     * prependTo
     *
     * @param  array|object  $source
     * @param  mixed         $referenceId
     *
     * @return  NestedEntityInterface|T
     *
     * @throws \ReflectionException
     */
    public function prependTo(mixed $source, mixed $referenceId): NestedEntityInterface
    {
        $entity = $this->sourceToEntity($source);

        return $this->setPositionPrependTo($source, $referenceId)
            ->saveOne($entity);
    }

    /**
     * @param  array|object  $source
     * @param  mixed         $referenceId
     *
     * @return  NestedEntityInterface
     *
     * @throws \ReflectionException
     */
    public function appendTo(mixed $source, mixed $referenceId): NestedEntityInterface
    {
        $entity = $this->sourceToEntity($source);

        return $this->setPositionAppendTo($source, $referenceId)
            ->saveOne($entity);
    }

    /**
     * @param  array|object  $source
     * @param  mixed         $referenceId
     *
     * @return  NestedEntityInterface|T
     *
     * @throws \ReflectionException
     */
    public function putBefore(mixed $source, mixed $referenceId): NestedEntityInterface
    {
        $entity = $this->sourceToEntity($source);

        return $this->setPositionBeforeOf($source, $referenceId)
            ->saveOne($entity);
    }

    /**
     * @param  array|object  $source
     * @param  mixed         $referenceId
     *
     * @return  NestedEntityInterface|T
     *
     * @throws \ReflectionException
     */
    public function putAfter(mixed $source, mixed $referenceId): NestedEntityInterface
    {
        $entity = $this->sourceToEntity($source);

        return $this->setPositionAfterOf($source, $referenceId)
            ->saveOne($entity);
    }

    protected function postProcessDelete(AfterDeleteEvent $event): void
    {
        $this->depth++;

        /** @var NestedEntityInterface|MultiTreeNestedEntityInterface|null $entity */
        $entity = $event->getEntity();

        if ($entity === null) {
            // No entity, unable to delete children.
            return;
        }

        if ($this->hasEvents(BeforeDeleteEvent::class, AfterDeleteEvent::class)) {
            // For triggering delete events, we loop all children and delete per-item.
            $iter = $this->select()
                ->where('lft', 'between', [$entity->getLft(), $entity->getRgt()])
                ->tapIf(
                    $this->isMultiTree(),
                    fn (Query $query) => $query->where('root_id', $entity->getRootId())
                )
                ->getIterator($this->getMetadata()->getClassName());

            foreach ($iter as $item) {
                $this->deleteWhere($item);
            }
        } else {
            // No events found, just delete all children.
            $this->delete()
                ->where('lft', 'between', [$entity->getLft(), $entity->getRgt()])
                ->tapIf(
                    $this->isMultiTree(),
                    fn (Query $query) => $query->where('root_id', $entity->getRootId())
                )
                ->execute();
        }

        // Since this process uses event, we must make sure lft/rgt offset is only run at top level event.
        if ($this->depth === 1) {
            // Compress the left values.
            $this->update()
                ->set('lft', raw('lft - ' . $entity->getWidth()))
                ->where('lft', '>', $entity->getLft())
                ->tapIf(
                    $this->isMultiTree(),
                    fn (Query $query) => $query->where('root_id', $entity->getRootId())
                )
                ->execute();

            // Compress the right values.
            $this->update()
                ->set('rgt', raw('rgt - ' . $entity->getWidth()))
                ->where('rgt', '>', $entity->getRgt())
                ->tapIf(
                    $this->isMultiTree(),
                    fn (Query $query) => $query->where('root_id', $entity->getRootId())
                )
                ->execute();
        }

        $this->depth--;
    }

    /**
     * @return  NestedEntityInterface|T|null
     */
    public function getRoot(mixed $conditions = []): ?NestedEntityInterface
    {
        /** @var NestedEntityInterface $root */
        $root = $this->select()
            ->where('parent_id', $this->getEmptyParentId())
            ->where($this->conditionsToWheres($conditions))
            ->get($this->getMetadata()->getClassName());

        return $root;
    }

    /**
     * rebuild
     *
     * @param  mixed|null  $source
     * @param  int|null  $lft
     * @param  int  $level
     * @param  string|null  $path
     *
     * @return  int  Return the right value of this node + 1
     */
    public function rebuild(mixed $source = null, ?int $lft = null, int $level = 0, ?string $path = null): int
    {
        $buildPath = $this->isPathable();

        if ($source === null) {
            $parent = $this->getRoot();
        } else {
            $parent = $this->sourceToEntity($source);
        }

        $parentData = $this->extract($parent);
        $parentId = $parentData[$this->getMainKey()];
        $lft = $lft ?? $parentData['lft'];

        if ($buildPath) {
            $path = $path ?? $parentData['path'];
        }

        // Build the structure of the recursive query.
        $query = $this->getRebuildQuery($parentId);

        // Assemble the query to find all children of this node.
        $children = $query->all($this->getMetadata()->getClassName());

        // The right value of this node is the left value + 1
        $rgt = $lft + 1;

        /** @var NestedPathableInterface $child */
        foreach ($children as $child) {
            /*
             * $rgt is the current right value, which is incremented on recursion return.
             * Increment the level for the children.
             * Add this item's alias to the path (but avoid a leading /)
             */
            $rgt = $this->rebuild(
                $child,
                $rgt,
                $level + 1,
                $buildPath
                    ? ltrim($path . '/' . $child->getAlias(), '/')
                    : null
            );
        }

        // We've got the left value, and now that we've processed
        // the children of this node we also know the right value.
        $this->update()
            ->set('lft', $lft)
            ->set('rgt', $rgt)
            ->set('level', $level)
            ->pipeIf($buildPath, fn(Query $query) => $query->set('path', $path))
            ->where($this->getMainKey(), $parentId)
            ->tapIf(
                $this->isMultiTree(),
                fn (Query $query) => $query->where('root_id', $parent->getRootId())
            )
            ->execute();

        // Return the right value of this node + 1.
        return $rgt + 1;
    }

    public function isPathable(): bool
    {
        return is_subclass_of(
            $this->getMetadata()->getClassName(),
            NestedPathableInterface::class
        );
    }

    public function calculatePath(array|object $entity): ?string
    {
        if (is_object($entity) && !$entity instanceof NestedPathableInterface) {
            return null;
        }

        $parentId = $this->extractField($entity, 'parent_id');
        $alias = $this->extractField($entity, 'alias');

        // Build the path.
        $path = $this->preparePath($parentId);

        return ltrim($path . '/' . $alias, '/');
    }

    public function rebuildPath(mixed $source, ?string $path = null): static
    {
        // todo: recursive to children
        $entity = $this->sourceToEntity($source);

        if (!$entity instanceof NestedPathableInterface) {
            return $this;
        }

        $pk = $this->extractField($entity, $this->getMainKey());

        // Build the path.
        $path = $path ?? $this->preparePath($pk);

        // Update the path field for the node.
        $this->update()
            ->set('path', $path)
            ->where($this->getMainKey(), $pk)
            ->execute();

        // Build the structure of the recursive query.
        $query = $this->getRebuildQuery($pk);

        // Assemble the query to find all children of this node.
        $children = $query->all($this->getMetadata()->getClassName());

        /** @var NestedPathableInterface $child */
        foreach ($children as $child) {
            /*
             * $rgt is the current right value, which is incremented on recursion return.
             * Increment the level for the children.
             * Add this item's alias to the path (but avoid a leading /)
             */
            $this->rebuildPath(
                $child,
                ltrim($path . '/' . $child->getAlias(), '/')
            );
        }

        // Update the current record's path to the new one:
        $entity->setPath((string) $path);

        return $this;
    }

    protected function preparePath(mixed $pk): ?string
    {
        if (!$pk) {
            return null;
        }

        // Get the aliases for the path from the node to the root node.
        $path = $this->getPath($pk);
        $segments = $path->column('alias');
        $segments->shift();

        // Build the path.
        return (string) $segments->implode('/')->trim('/\\');
    }

    /**
     * @param  array  $data
     *
     * @return  NestedEntityInterface|T
     *
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function createRoot(array $data = []): NestedEntityInterface
    {
        $data = array_merge(
            [
                'parent_id' => null,
                'lft' => 0,
                'rgt' => 1,
                'level' => 0,
                'is_root' => true,
            ],
            $data
        );

        if ($this->isPathable()) {
            $data['path'] ??= '';
            $data['alias'] ??= 'root';
        }

        /** @var NestedEntityInterface $root */
        $root = $this->createOne($data);

        if ($this->isMultiTree()) {
            $this->updateWhere(
                ['root_id' => $root->getPrimaryKeyValue()],
                ['id' => $root->getPrimaryKeyValue()]
            );
        }

        return $root;
    }

    /**
     * @param  array  $data
     * @param  array  $condFields
     *
     * @return NestedEntityInterface
     *
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function createRootIfNotExist(array $data = [], array $condFields = ['title']): NestedEntityInterface
    {
        $conditions = [];

        if ($this->isMultiTree()) {
            $conditions = Arr::only($data, $condFields);
        }

        $root = $this->getRoot($conditions);

        if ($root) {
            return $root;
        }

        return $this->createRoot($data);
    }

    /**
     * sourceToEntity
     *
     * @param  mixed  $source
     *
     * @return  object|NestedEntityInterface|T
     */
    private function sourceToEntity(mixed $source): object
    {
        if (is_object($source)) {
            return $source;
        }

        if (is_array($source)) {
            $pk = $source[$this->getMainKey()] ?? null;
        } else {
            $pk = $source;
        }

        // Get the node by id.
        return $this->findOne($pk);
    }

    /**
     * @param  mixed|null  $conditions
     *
     * @return  void
     */
    protected function checkRootExists(mixed $conditions = null): void
    {
        $root = $this->getRoot($conditions);

        if ($root) {
            throw new NestedHandleException('Root has already exists.');
        }
    }

    /**
     * @param  int|null  $parentId
     *
     * @return  SelectorQuery
     */
    protected function getRebuildQuery(?int $parentId): SelectorQuery
    {
        $query = $this->cacheStorage['rebuild.sql'] ??= $this->createSelectorQuery()
            ->select('n.*')
            ->from(
                [
                    [$this->getMetadata()->getClassName(), 'n'],
                    [$this->getMetadata()->getClassName(), 'p'],
                ]
            )
            ->whereRaw('n.parent_id = p.id')
            ->whereRaw('n.parent_id = :parent_id')
            ->tapIf(
                $this->isMultiTree(),
                fn(Query $query) => $query->whereRaw('n.root_id = p.root_id')
            )
            ->order('n.parent_id')
            ->order('n.lft');

        return $query->bind('parent_id', $parentId);
    }

    private function sourceToPk(mixed $source): mixed
    {
        if (is_object($source) || is_array($source)) {
            return $this->extractField($source, $this->getMainKey());
        }

        return $source;
    }

    protected function getEmptyParentId(): mixed
    {
        $column = $this->getDb()
            ->getTableManager($this->getMetadata()->getTableName())
            ->getColumn('parent_id');

        if (!$column) {
            throw new NestedHandleException('Column parent_id not exists.');
        }

        if ($column->getIsNullable()) {
            return null;
        }

        if ($column->isNumeric()) {
            return 0;
        }

        return '';
    }

    protected function getTreeRepositionData(
        NestedEntityInterface|MultiTreeNestedEntityInterface $reference,
        int $width,
        NestedPosition $position
    ): array {
        if ($width < 2) {
            throw new NestedHandleException('Node width less than 2.');
        }

        $k = $this->getMainKey();
        $result = [];

        switch ($position) {
            case Position::FIRST_CHILD:
                $leftWhere = ['lft', '>', $reference->getLft()];
                $rightWhere = ['rgt', '>=', $reference->getLft()];

                $result['lft'] = $reference->getLft() + 1;
                $result['rgt'] = $reference->getLft() + $width;
                $result['parent_id'] = $reference->getPrimaryKeyValue();
                $result['level'] = $reference->getLevel() + 1;
                break;

            case Position::LAST_CHILD:
                $leftWhere = ['lft', '>', $reference->getRgt()];
                $rightWhere = ['rgt', '>=', $reference->getRgt()];

                $result['lft'] = $reference->getRgt();
                $result['rgt'] = $reference->getRgt() + $width - 1;
                $result['parent_id'] = $reference->getPrimaryKeyValue();
                $result['level'] = $reference->getLevel() + 1;
                break;

            case Position::BEFORE:
                if ($reference->isRoot()) {
                    throw new InvalidArgumentException(
                        'Can not set before a root node.'
                    );
                }

                $leftWhere = ['lft', '>=', $reference->getLft()];
                $rightWhere = ['rgt', '>=', $reference->getLft()];

                $result['lft'] = $reference->getLft();
                $result['rgt'] = $reference->getLft() + $width - 1;
                $result['parent_id'] = $reference->getParentId();
                $result['level'] = $reference->getLevel();
                break;

            case Position::AFTER:
            default:
                if ($reference->isRoot()) {
                    throw new InvalidArgumentException(
                        'Can not set after a root node.'
                    );
                }

                $leftWhere = ['lft', '>', $reference->getRgt()];
                $rightWhere = ['rgt', '>', $reference->getRgt()];

                $result['lft'] = $reference->getRgt() + 1;
                $result['rgt'] = $reference->getRgt() + $width;
                $result['parent_id'] = $reference->getParentId();
                $result['level'] = $reference->getLevel();
                break;
        }

        $baseWhere = [];

        if ($this->isMultiTree()) {
            $result['root_id'] = $rootId = $reference->isRoot()
                ? $reference->getPrimaryKeyValue()
                : $reference->getRootId();

            $baseWhere = [
                ['root_id', $rootId]
            ];
        }

        return [$result, $leftWhere, $rightWhere, $baseWhere];
    }

    public function emitEvent(string|EventInterface $event, array $args = []): EventInterface
    {
        return parent::emitEvent($event, $args);
    }

    public function isMultiTree(): bool
    {
        return is_a($this->getMetadata()->getClassName(), MultiTreeNestedEntityInterface::class, true);
    }
}
