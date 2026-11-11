<p align="center">
    <br/>
    <img src="https://user-images.githubusercontent.com/1639206/151679867-8df93936-e4af-4677-a6f3-eb33d27e038b.svg" alt="Windwalker"
        height="75">
    <br/>
</p>

<h2 align="center">Di</h2>

<p align="center">
    Windwalker DI package
</p>

<p align="center">
    <img alt="GitHub" src="https://img.shields.io/github/license/windwalker-io/di?style=flat-square">
    <img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/windwalker-io/di/ci.yml?label=test&style=flat-square">
    <img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/windwalker/di?style=flat-square">
    <img alt="Packagist Version" src="https://img.shields.io/packagist/v/windwalker/di?style=flat-square">
</p>

### Installation

```bash
composer require windwalker/di ^4.0
```

### Child parameter scopes

#### Why branches are overlaid

Request-local configuration changes must not mutate shared parent configuration.
Previously, after a child called `setDeep('user.remember.cookie_name', 'ADMIN')`,
reading `getDeep('user.remember')` returned only `cookie_name`: the non-null local
array prevented fallback to the parent's `enabled` and `expires` settings.
Reading a complete leaf path could still succeed, so extracting or passing a
whole branch produced different results from reading individual settings.

Container children inherit parameters from their parent. Writing a deep path only
changes the child's local storage. `get()`, `getDeep()`, `dump()` and iteration
overlay local array branches on inherited branches, so sibling settings remain
available. Parent changes remain visible unless overridden locally.

#### Merge policy and compatibility

Array overlays follow `Arr::mergeRecursive()`: associative branches merge
recursively, lists concatenate, and an empty local array does not clear an
inherited array. Non-null scalar values override inherited values. Leaf reads
retain the existing `null` fallback; array snapshots retain explicit null entries
as `dump()` does.

Reusing the existing dump policy avoids introducing a second array merge policy
for branch reads. The null distinction is intentionally retained for compatibility:
reading a null leaf can fall back even when a returned branch contains that null.
This overlay is not a deletion API; setting an empty array does not remove a branch.

#### Snapshots, proxies and references

`extract()` returns a detached snapshot of the merged branch. `proxy()` (or
`extract($path, true)`) keeps a live view and writes into the owning scope's local
storage, not its ancestor's storage. Proxy dumps and iteration contain only the
selected branch. Array overlays and inherited iteration are computed snapshots;
use `set()` / `setDeep()` or a proxy to write changes rather than modifying a
returned merged array by reference. `getIterator(false)` exposes local storage
without inherited values.

Extracting from the merged view prevents inherited siblings from disappearing in
the snapshot, while retaining Collection's rejection of scalar extraction.
Proxies reference local storage instead of materializing inherited branches, so
writes stay isolated and later parent updates remain visible. Their parent view
already contains local data; merging that data again would duplicate list items.
Proxy path prefixes are delimiter-neutral so a dotted prefix also supports deep
reads with a custom delimiter.

Inherited iteration yields each merged key once. Previously, yielding parent
keys after local keys let array collectors overwrite child values with parent
values. Merged iteration is a snapshot because a combined branch has no single
backing storage to reference.

Aliases are resolved after inheritance lookup, in the reading scope. This lets
an inherited reference observe a child override instead of resolving prematurely
against the parent. Dumps use the same scoped resolution and restrict proxy
output to its selected branch rather than including unrelated parent settings.

### Resources

- [Documentation](https://windwalker.io/documentation/components/di/)
- [Bug report](https://github.com/windwalker-io/framework)
- [Contributing](https://github.com/windwalker-io/framework)
- [Discussion](https://github.com/windwalker-io/framework/discussions)
