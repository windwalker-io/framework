<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\ORM\ORM;

/**
 * The AbstractORMTestCase class.
 */
abstract class AbstractORMTestCase extends AbstractDatabaseTestCase
{
    public static ORM $orm;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$db = self::createAdapter();
        static::$orm = new ORM(static::$db);
    }
}
