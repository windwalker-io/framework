<p align="center">
    <br/>
    <img src="https://user-images.githubusercontent.com/1639206/151679867-8df93936-e4af-4677-a6f3-eb33d27e038b.svg" alt="Windwalker"
        height="75">
    <br/>
</p>

<h2 align="center">Database</h2>

<p align="center">
    Windwalker Database package
</p>

<p align="center">
    <img alt="GitHub" src="https://img.shields.io/github/license/windwalker-io/database?style=flat-square">
    <img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/windwalker-io/database/ci.yml?label=test&style=flat-square">
    <img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/windwalker/database?style=flat-square">
    <img alt="Packagist Version" src="https://img.shields.io/packagist/v/windwalker/database?style=flat-square">
</p>

### Installation

```bash
composer require windwalker/database ^4.0
```

### Lost connections

`AbstractDriver::useConnection()` reconnects and retries its callback once when
execution reports a lost connection. Detection includes MySQL gone-away/lost-
connection errors, timeouts, connection reset by peer, unexpectedly closed
server/SSL connections, communication-link failures, and query-packet send errors.
Initial connection failures, failed reconnects, and failed retries propagate to
the caller; this is not an unlimited retry loop.

The callback can execute SQL again. A disconnect does not restore a transaction,
and retrying a write after an ambiguous response can duplicate effects. Callers
must handle failed operations and use idempotent writes where necessary.

### Resources

- [Documentation](https://windwalker.io/documentation/components/database/)
- [Bug report](https://github.com/windwalker-io/framework)
- [Contributing](https://github.com/windwalker-io/framework)
- [Discussion](https://github.com/windwalker-io/framework/discussions)
