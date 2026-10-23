<p align="center">
    <br/>
    <img src="https://user-images.githubusercontent.com/1639206/151679867-8df93936-e4af-4677-a6f3-eb33d27e038b.svg" alt="Windwalker"
        height="75">
    <br/>
</p>

<h2 align="center">Session</h2>

<p align="center">
    Windwalker Session package
</p>

<p align="center">
    <img alt="GitHub" src="https://img.shields.io/github/license/windwalker-io/session?style=flat-square">
    <img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/windwalker-io/session/ci.yml?label=test&style=flat-square">
    <img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/windwalker/session?style=flat-square">
    <img alt="Packagist Version" src="https://img.shields.io/packagist/v/windwalker/session?style=flat-square">
</p>

### Installation

```bash
composer require windwalker/session ^4.0
```

### Session security

- `Session::regenerate()` and both bridges delete the previous session by default. Native bridge `restart()` also
  deletes the previous session by default. Pass `deleteOld: false` explicitly only when retaining the old ID is intended;
  `Session::fork()` preserves its source session.
- Filesystem, database and PDO handlers reject expired sessions when reading, even before garbage collection runs.
  The idle lifetime in seconds comes from the handler's `gc_maxlifetime` option, falling back to PHP's
  `session.gc_maxlifetime` setting. Refreshing the timestamp extends the idle lifetime; this is not an absolute login timeout.
- Redis writes and timestamp updates use the handler's `ttl` option, then its `gc_maxlifetime` option, then PHP's
  `session.gc_maxlifetime`. The selected TTL must be a positive integer; integer strings are supported.

### Resources

- [Documentation](https://windwalker.io/documentation/components/session/)
- [Bug report](https://github.com/windwalker-io/framework)
- [Contributing](https://github.com/windwalker-io/framework)
- [Discussion](https://github.com/windwalker-io/framework/discussions)

