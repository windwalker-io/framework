<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Relation\Strategy;

use Windwalker\ORM\Metadata\EntityMetadata;

/**
 * Interface RelationConfigureInterface
 */
interface RelationConfigureInterface
{
    public function targetTo(?string $table, mixed ...$columns): static;

    public function foreignKeys(mixed ...$columns): static;

    /**
     * @param  bool  $flush
     *
     * @return  static  Return self to support chaining.
     */
    public function flush(bool $flush): static;

    /**
     * @param  string  $propName
     *
     * @return  static  Return self to support chaining.
     */
    public function propName(string $propName): static;

    /**
     * @param  string  $onUpdate
     *
     * @return  static  Return self to support chaining.
     */
    public function onUpdate(string $onUpdate): static;

    /**
     * @param  string  $onDelete
     *
     * @return  static  Return self to support chaining.
     */
    public function onDelete(string $onDelete): static;

    /**
     * @param  array  $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(array $options): static;

    /**
     * morphBy
     *
     * @param  mixed  ...$columns
     *
     * @return  static
     */
    public function morphBy(mixed ...$columns): static;

    /**
     * @param  EntityMetadata  $metadata
     *
     * @return  static  Return self to support chaining.
     */
    public function setMetadata(EntityMetadata $metadata): static;

    /**
     * @param  string  $propName
     *
     * @return  static  Return self to support chaining.
     */
    public function setPropName(string $propName): static;
}
