<?php

declare(strict_types=1);

namespace Windwalker\Database\Platform;

enum DbPlatform
{
    case MySQL;
    case PostgreSQL;
    case SQLite;
    case SQLServer;
}
