<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Attribute;

/**
 * Before data really store to DB event.
 */
#[Attribute]
class BeforeStoreEvent extends AbstractSaveEvent
{
    //
}
