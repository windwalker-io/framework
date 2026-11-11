<?php

declare(strict_types=1);

namespace Windwalker\DI;

use Generator;
use Windwalker\Data\Collection;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\TypeCast;
use Windwalker\Utilities\Wrapper\ValueReference;

/**
 * The Config class.
 */
class Parameters extends Collection
{
    /**
     * Make sure storage is first.
     *
     * @var  array
     */
    protected mixed $storage = [];

    protected ?Parameters $parent = null;

    protected string $path = '';

    public function createChild(): static
    {
        $new = clone $this;
        $new->parent = $this;

        return $new;
    }

    public function addPath(string $path): void
    {
        // Keep proxy prefixes delimiter-neutral so dotted prefixes also work with getDeep(..., '/').
        $path = str_replace('.', '{SEPARATE}', $path);

        if (!$this->path) {
            $this->path = $path;

            return;
        }

        $this->path .= '{SEPARATE}' . $path;
    }

    /**
     * @inheritDoc
     */
    public function extract(?string $path = null, bool $reference = false): static
    {
        if ($reference) {
            // Reference only local storage: copying inherited values here would freeze parent updates.
            $new = parent::extract($path, true);
            $new->parent = $this;
        } else {
            // Extract the effective branch, not the partial local branch. Collection preserves
            // extract's type validation while the new Parameters remains a detached snapshot.
            $snapshot = new Collection($this->getEffectiveStorage())->extract($path);
            $new = new static($snapshot->dump());
        }

        // A null path extracts the whole scope and must not introduce a proxy prefix.
        $new->addPath((string) $path);

        return $new;
    }

    /**
     * getDeep
     *
     * @param  string  $path
     * @param  string  $delimiter
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function &getDeep(string $path, string $delimiter = '.', int $depth = 0): mixed
    {
        $value = parent::getDeep($path, $delimiter);

        // A deep local write creates partial arrays; null-only fallback would hide inherited siblings.
        if (($value === null || is_array($value)) && $this->parent) {
            $depth++;
            $inherited = $this->parent->getDeep($this->getParentPath($path, $delimiter), $delimiter, $depth);

            if ($inherited === null && $value === null) {
                // Retain unscoped fallback for missing proxy values, but do not merge an unrelated
                // root branch into a local array when the scoped parent branch is absent.
                $inherited = $this->parent->getDeep($path, $delimiter, $depth);
            }

            // A proxy's scoped parent already includes its referenced local storage.
            // Merging it again would concatenate local list entries twice.
            $value = $this->isProxy && is_array($inherited)
                ? $inherited
                : $this->mergeInheritedValue($inherited, $value);
            $depth--;
        }

        if ($depth === 0) {
            // Only extract ref on leaf
            while ($value instanceof ValueReference) {
                $value = $value($this, $value->getDelimiter() ?? $delimiter);
            }
        }

        return $value;
    }

    /**
     * Get value from this object.
     *
     * @param  mixed  $key
     *
     * @return  mixed
     */
    public function &get(mixed $key): mixed
    {
        // Resolve references only after inheritance lookup so parent aliases see child overrides.
        $value = $this->getRawValue($key);

        while ($value instanceof ValueReference) {
            $value = $value($this);
        }

        return $value;
    }

    /**
     * Read a literal key without resolving aliases in an ancestor's scope.
     *
     * This gives get() the same overlay behavior as getDeep(), while keeping keys
     * containing dots literal and resolving proxy keys relative to their selected branch.
     */
    protected function getRawValue(mixed $key): mixed
    {
        $value = parent::get($key);

        if (($value === null || is_array($value)) && $this->parent) {
            $inherited = null;

            if ($this->path !== '') {
                $branch = $this->parent->getDeep(str_replace('{SEPARATE}', '.', $this->path), depth: 1);
                $inherited = is_array($branch) ? ($branch[$key] ?? null) : null;
            }

            if ($inherited === null && ($value === null || $this->path === '')) {
                $inherited = $this->parent->getRawValue($key);
            }
            // Scoped proxy reads already include local arrays; avoid duplicating list entries.
            $value = $this->isProxy && is_array($inherited)
                ? $inherited
                : $this->mergeInheritedValue($inherited, $value);
        }

        return $value;
    }

    /**
     * Preserve inherited siblings using the existing dump() array merge policy.
     *
     * Lists concatenate and empty arrays do not clear inherited arrays. Scalars
     * still override directly; null retains the existing leaf fallback contract.
     */
    protected function mergeInheritedValue(mixed $inherited, mixed $local): mixed
    {
        if (is_array($inherited) && is_array($local)) {
            return Arr::mergeRecursive($inherited, $local);
        }

        return $local ?? $inherited;
    }

    /**
     * Build an uncached overlay for snapshots, dumps and inherited iteration.
     *
     * Keeping merged arrays out of local storage preserves scope isolation and
     * visibility of later parent updates. Returned arrays are not writable proxies.
     */
    protected function getEffectiveStorage(): array
    {
        if (!$this->parent) {
            return $this->storage;
        }

        $inherited = $this->parent->getEffectiveStorage();

        if ($this->path !== '') {
            // A proxy represents only its selected branch, not the parent's entire namespace.
            $inherited = Arr::get($inherited, str_replace('{SEPARATE}', '.', $this->path));
        }

        if (!is_array($inherited)) {
            return $this->storage;
        }

        if ($this->isProxy) {
            // Local proxy storage is already part of the parent's effective branch.
            return $inherited;
        }

        return Arr::mergeRecursive($inherited, $this->storage);
    }

    public function hasDeep(string $path, ?string $delimiter = '.'): bool
    {
        return $this->getDeep($path, $delimiter) !== null;
    }

    public function has(mixed $key): bool
    {
        return $this->get($key) !== null;
    }

    public function getParentPath(string $path, string $delimiter = '.'): string
    {
        if ($this->path) {
            $path = $this->path . '{SEPARATE}' . $path;
        }

        return str_replace('{SEPARATE}', $delimiter, $path);
    }

    /**
     * Method to get property Parent
     *
     * @return  Parameters|null
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getParent(): ?Parameters
    {
        return $this->parent;
    }

    /**
     * @inheritDoc
     */
    public function &getIterator(bool $includeParent = true): Generator
    {
        if ($includeParent && $this->parent) {
            // Yield merged keys once: yielding parent keys afterwards used to overwrite
            // child overrides when consumers collected the iterator into an array.
            $data = $this->getEffectiveStorage();

            foreach ($data as $key => &$value) {
                yield $key => $value;
            }

            return;
        }

        // Local-only iteration retains the raw storage/reference behavior without inheritance.
        foreach ($this->storage as $key => &$value) {
            yield $key => $value;
        }
    }

    /**
     * Creates a copy of storage.
     *
     * @param  bool  $recursive
     *
     * @param  bool  $onlyDumpable
     *
     * @return  array
     */
    public function dump(bool $recursive = false, bool $onlyDumpable = false): array
    {
        // Merge before resolving aliases so inherited references use child overrides,
        // and proxy dumps do not leak unrelated parent branches.
        $data = $this->getEffectiveStorage();

        $data = Arr::mapRecursive(
            $data,
            function ($v) {
                if ($v instanceof ValueReference) {
                    // Resolve through Parameters to retain scoped and ancestor fallback.
                    return $v($this);
                }

                return $v;
            },
        );

        if ($recursive) {
            $data = TypeCast::toArray($data, true, $onlyDumpable);
        }

        return $data;
    }
}
