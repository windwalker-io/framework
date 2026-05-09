# Docker Development Environment

This project provides a Docker Compose setup so developers can quickly spin up all external services required for testing.

## Included Services

| Service       | Image                  | Default Port |
|---------------|------------------------|--------------|
| MySQL 8.4     | `mysql:8.4`            | 3306         |
| PostgreSQL 17 | `postgres:17-alpine`   | 5432         |
| Redis 7       | `redis:7-alpine`       | 6379         |
| Memcached 1   | `memcached:1-alpine`   | 11211        |

## Quick Start

### 1. Start all services

```bash
docker compose up -d
```

### 2. Verify services are ready

```bash
docker compose ps
```

### 3. Configure phpunit.xml

Copy `phpunit.xml.dist` to `phpunit.xml`, then uncomment the DSN constants you need:

```bash
cp phpunit.xml.dist phpunit.xml
```

Edit `phpunit.xml` and uncomment the relevant `<const>` and `<env>` entries, for example:

```xml
<!-- MySQL (Docker) -->
<const name="WINDWALKER_TEST_DB_DSN_MYSQL"
    value="host=127.0.0.1;dbname=windwalker_test;user=root;password=ut1234;prefix=ww_" />

<!-- PostgreSQL (Docker) -->
<const name="WINDWALKER_TEST_DB_DSN_POSTGRESQL"
    value="host=127.0.0.1;dbname=windwalker_test;user=postgres;password=ut1234;prefix=ww_" />

<!-- SQLite (no extra service needed) -->
<const name="WINDWALKER_TEST_DB_DSN_SQLITE" value="dbname=tmp/test.db;prefix=ww_" />

<!-- Enable Redis / Memcached tests -->
<env name="REDIS_ENABLED" value="1" />
<env name="MEMCACHED_ENABLED" value="1" />
```

### 4. Run the tests

```bash
php vendor/bin/phpunit
```

---

## Port Conflicts / Custom Ports

If you already have services installed locally (e.g. MySQL, Redis) and the default ports conflict, you have two options:

### Option A: Override ports via `.env`

Copy and edit `.env.docker`:

```bash
cp .env.docker .env
```

Uncomment and change the port(s) you need:

```dotenv
MYSQL_PORT=3307
REDIS_PORT=6380
```

Then update the DSN in `phpunit.xml` to use the new port, e.g. `host=127.0.0.1;port=3307;...`.

### Option B: Use `docker-compose.override.yml`

Copy the override template and edit it:

```bash
cp docker-compose.override.yml.dist docker-compose.override.yml
```

Inside the override file you can remap ports or remove a service entirely so that `phpunit.xml` connects to your locally installed service instead.

---

## Stopping Services

```bash
# Stop containers, keep data volumes
docker compose stop

# Stop and remove containers (data volumes are preserved)
docker compose down

# Stop and remove everything including data volumes
docker compose down -v
```

## Connection Reference

| Service    | Host      | Port  | User     | Password | Database        |
|------------|-----------|-------|----------|----------|-----------------|
| MySQL      | 127.0.0.1 | 3306  | root     | ut1234   | windwalker_test |
| PostgreSQL | 127.0.0.1 | 5432  | postgres | ut1234   | windwalker_test |
| Redis      | 127.0.0.1 | 6379  | —        | —        | —               |
| Memcached  | 127.0.0.1 | 11211 | —        | —        | —               |

