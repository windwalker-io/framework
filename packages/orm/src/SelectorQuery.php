<?php

declare(strict_types=1);

namespace Windwalker\ORM;

use InvalidArgumentException;
use Windwalker\Attributes\AttributesAccessor;
use Windwalker\Data\Collection;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Event\HydrateEvent;
use Windwalker\Database\Event\ItemFetchedEvent;
use Windwalker\Event\EventAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\ORM\Attributes\UseRealColumns;
use Windwalker\ORM\Attributes\UUIDBin;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\Relation\Strategy\ManyToMany;
use Windwalker\Query\Clause\AsClause;
use Windwalker\Query\Clause\ClauseInterface;
use Windwalker\Query\Clause\JoinClause;
use Windwalker\Query\Query;
use Windwalker\Utilities\Arr;

use function Windwalker\Query\try_uuid2bin;
use function Windwalker\Query\val;

use const Windwalker\Query\QN_IGNORE_DOTS;

/**
 * The SelectAction class.
 *
 * @property-read DatabaseAdapter $db
 * @property-read ORM             $orm
 *
 * Query methods.
 */
class SelectorQuery extends Query implements EventAwareInterface
{
    use EventAwareTrait;

    protected ?string $groupDivider = null;

    public static bool $emptyCollectionAsNull = true;

    protected bool $loadColumnsFromDb = false;

    /**
     * @inheritDoc
     */
    public function __construct(ORM $orm, $grammar = null)
    {
        parent::__construct($orm, $grammar ?? $orm->getDb()->getPlatform()->getGrammar());

        $this->bindEvents();
    }

    protected function bindEvents(): void
    {
        $this->on(
            ItemFetchedEvent::class,
            function (ItemFetchedEvent $event) {
                if ($this->groupDivider !== null) {
                    $event->item = $this->groupItem($event->item);
                }
            }
        );

        $this->on(
            HydrateEvent::class,
            function (HydrateEvent $event) {
                $orm = $this->getORM();
                $item = $event->item;

                if ($item === null) {
                    return;
                }

                $object = $event->class;

                if (is_string($object)) {
                    if (EntityMetadata::isEntity($object)) {
                        $object = $orm->createEntity($object);
                    } else {
                        $object = new $object();
                    }
                }

                $object = $orm->hydrateEntity($item, $object);

                if (EntityMetadata::isEntity($object)) {
                    // Prepare relations
                    $orm->getEntityMetadata($object)
                        ->getRelationManager()
                        ->load($item, $object);
                }

                $event->item = $object;
            }
        );
    }

    public function autoSelections(
        string $divider = ':',
        ?array &$columns = null,
        ?bool $loadColumnsFromDb = null
    ): static {
        $columns ??= [];

        /** @var array<int, AsClause> $tables */
        $tables = array_values(Arr::collapse($this->getAllTables()));

        $db = $this->getDb();

        foreach ($tables as $i => $clause) {
            $className = $clause->getValue();

            if ($className instanceof Query) {
                continue;
            }

            $tableName = static::convertClassToTable($className, $alias);

            $loadColumnsFromDb ??= $this->shouldLoadColumnsFromDb($className);

            if ($loadColumnsFromDb) {
                $cols = $db->getTableManager($tableName)->getColumnNames();
            } else {
                $cols = array_keys($this->orm->getEntityMetadata($className)->getPureColumns());
            }

            foreach ($cols as $col) {
                $alias = $clause->getAlias() ?? $alias;

                if ($i === 0) {
                    $as = $col;
                } else {
                    $as = $alias . $divider . $col;
                }

                $columns[] = $alias . $divider . $col;

                $this->selectRaw(
                    '%n AS %r',
                    $alias . '.' . $col,
                    $this->quoteName($as, QN_IGNORE_DOTS)
                );
            }
        }

        return $this;
    }

    public function groupByDivider(?string $divider = ':'): static
    {
        $this->groupDivider = $divider;

        return $this;
    }

    public function groupByJoins(string $divider = ':', ?bool $loadColumnsFromDb = null): static
    {
        return $this->autoSelections($divider, loadColumnsFromDb: $loadColumnsFromDb)
            ->groupByDivider($divider);
    }

    protected function groupItem(?array $item): ?array
    {
        if ($item === null) {
            return null;
        }

        /** @var Collection[] $subItems */
        $subItems = [];

        foreach ($item as $k => $value) {
            if (str_contains($k, $this->groupDivider)) {
                [$prefix, $key] = explode($this->groupDivider, $k, 2);

                $subItem = $subItems[$prefix] ??= new Collection();

                $item[$prefix] = $subItem;

                $subItem[$key] = $value;

                unset($item[$k]);
            }
        }

        if (static::$emptyCollectionAsNull) {
            foreach ($subItems as $prefix => $subItem) {
                $subItem = $subItem->filter(fn($v) => $v !== null);

                if ($subItem->isEmpty()) {
                    $item[$prefix] = null;
                }
            }
        }

        return $item;
    }

    public function join(string $type, mixed $table, ?string $alias = null, ...$on): static
    {
        if (!$on && is_string($table) && class_exists($table)) {
            $on = $this->handleAutoJoin($type, $table, $alias, $on);
        }

        return parent::join($type, $table, $alias, ...$on);
    }

    private function handleAutoJoin(string $type, string $table, ?string &$alias, array $on): array
    {
        /** @var AsClause|null $fromClause */
        $fromClause = $this->getFrom()?->getElements()[0] ?? null;
        $from = $fromClause?->getValue();

        if (!$from) {
            return $on;
        }

        $fromMetadata = $this->getORM()->getEntityMetadata($from);
        $joinMetadata = $this->getORM()->getEntityMetadata($table);
        $relation = null;

        $fromAlias = $fromClause->getAlias() ?: $fromMetadata->getTableAlias();
        $alias ??= $joinMetadata->getTableAlias();

        foreach ($fromMetadata->getRelationManager()->getRelations() as $relation) {
            if ($relation instanceof ManyToMany) {
                $mapMetadata = $relation->getMapMetadata();
                $mapAlias = $mapMetadata->getTableAlias();

                if ($relation->getMapTable() === $table) {
                    foreach ($relation->getMapForeignKeys() as $ok => $mfk) {
                        $on[] = ["$fromAlias.$ok", '=', "$alias.$mfk"];
                    }

                    foreach ($relation->getMap()->getMorphs() as $field => $value) {
                        $on[] = ["$mapAlias.$field", '=', val($value)];
                    }

                    return [$on];
                }

                if ($relation->getTargetTable() === $table) {
                    foreach ($relation->getForeignKeys() as $mk => $fk) {
                        $on[] = ["$mapAlias.$mk", '=', "$alias.$fk"];
                    }

                    foreach ($relation->getTarget()->getMorphs() as $field => $value) {
                        $on[] = ["$alias.$field", '=', val($value)];
                    }

                    return [$on];
                }
            }

            if ($relation->getTargetTable() === $table) {
                foreach ($relation->getForeignKeys() as $ok => $fk) {
                    $on[] = ["$fromAlias.$ok", '=', "$alias.$fk"];
                }

                foreach ($relation->getMorphs() as $field => $value) {
                    $on[] = ["$alias.$field", '=', val($value)];
                }

                return [$on];
            }
        }

        // No relations, guess by columns

        // many to one
        // example: articles join categories
        $myFk = $alias . '_id';
        $themPk = $joinMetadata->getMainKey();

        if ($themPk && $fromMetadata->getColumn($myFk)) {
            $on[] = ["$fromAlias.$myFk", '=', "$alias.$themPk"];

            return [$on];
        }

        // one to many
        // example: categories join articles
        $myPk = $fromMetadata->getMainKey();
        $themFk = $fromAlias . '_id';

        if ($myPk && $joinMetadata->getColumn($themFk)) {
            $on[] = ["$fromAlias.$myPk", '=', "$alias.$themFk"];

            return [$on];
        }

        return $on;
    }

    /**
     * Get Column Attribute and EntityMetadata from column string like: `article.id` or `id`.
     *
     * @param  string  $column
     *
     * @return  array{ 0: Attributes\Column|null, 1: EntityMetadata }
     */
    protected function getColumnInfoFromColumnString(string $column): array
    {
        $orm = $this->getORM();

        $colExtracted = explode('.', $column, 2);

        if (count($colExtracted) === 1) {
            /** @var AsClause|null $clause */
            $clause = $this->getFrom()?->getElements()[0] ?? null;
            $tableClass = $clause?->getValue() ?? '';
            $colName = $colExtracted[0];
        } else {
            $colName = $colExtracted[1];
            $joins = array_map(
                static fn(JoinClause $clause) => $clause->getTable(),
                $this->getJoin()?->getElements() ?? []
            );
            $clauses = [
                ...($this->getFrom()?->getElements() ?? []),
                ...($joins),
            ];

            /** @var AsClause|null $clause */
            $clause = array_find(
                $clauses,
                function (AsClause $clause) use ($colExtracted) {
                    self::convertClassToTable($clause->getValue(), $alias);

                    $alias = $clause->getAlias() ?? $alias;

                    return $alias === $colExtracted[0];
                }
            );

            $tableClass = $clause?->getValue() ?? '';
        }

        if (!is_string($tableClass) || $tableClass === '' || !class_exists($tableClass)) {
            return [null, null];
        }

        $metadata = $orm->getEntityMetadata($tableClass);

        $colAttr = $metadata->getColumn($colName);

        return [$colAttr, $metadata];
    }

    protected function handleOperatorAndValue(
        string|ClauseInterface $column,
        mixed $operator,
        mixed $value
    ): array {
        if ($value !== null && is_string($column)) {
            [$colAttr] = $this->getColumnInfoFromColumnString($column);

            if ($colAttr && $prop = $colAttr->getProperty()) {
                $uuidBinAttr = AttributesAccessor::getFirstAttribute(
                    $prop,
                    UUIDBin::class,
                    \ReflectionAttribute::IS_INSTANCEOF
                );

                if ($uuidBinAttr) {
                    $value = try_uuid2bin($value);
                }
            }
        }

        return parent::handleOperatorAndValue($column, $operator, $value);
    }

    public function getDb(): DatabaseAdapter
    {
        return $this->getORM()->getDb();
    }

    public function getORM(): ORM
    {
        return $this->getEscaper()->getConnection();
    }

    public function __get(string $name)
    {
        if ($name === 'db') {
            return $this->getDb();
        }

        if ($name === 'orm') {
            return $this->getORM();
        }

        throw new InvalidArgumentException(
            sprintf(
                'Property is %s undefined in %s',
                $name,
                static::class
            )
        );
    }

    /**
     * When an object is cloned, PHP 5 will perform a shallow copy of all of the object's properties.
     * Any properties that are references to other variables, will remain references.
     * Once the cloning is complete, if a __clone() method is defined,
     * then the newly created object's __clone() method will be called, to allow any necessary properties that need to
     * be changed. NOT CALLABLE DIRECTLY.
     *
     * @return void
     * @throws \ReflectionException
     * @link https://php.net/manual/en/language.oop5.cloning.php
     */
    public function __clone(): void
    {
        parent::__clone();

        $this->dispatcher = clone $this->dispatcher;

        $this->dispatcher->off(ItemFetchedEvent::class);
        $this->dispatcher->off(HydrateEvent::class);

        $this->bindEvents();
    }

    /**
     * createSubQuery
     *
     * @return  static
     */
    public function createSubQuery(): static
    {
        return new static($this->orm, $this->grammar);
    }

    public function shouldLoadColumnsFromDb(string $className): bool
    {
        if ($this->loadColumnsFromDb) {
            return true;
        }

        return !class_exists($className)
            || (
                class_exists($className)
                && AttributesAccessor::getFirstAttribute($className, UseRealColumns::class)
            );
    }

    public function isLoadColumnsFromDb(): bool
    {
        return $this->loadColumnsFromDb;
    }

    public function loadColumnsFromDb(bool $fetchColumnsFromDb = true): static
    {
        $this->loadColumnsFromDb = $fetchColumnsFromDb;

        return $this;
    }
}
