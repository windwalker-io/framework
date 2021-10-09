<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

use Windwalker\DI\Container;

return $this->instance->call($object->foo(...), [], null, Container::AUTO_WIRE);
