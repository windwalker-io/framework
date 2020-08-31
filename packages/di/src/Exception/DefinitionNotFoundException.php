<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * The DefinitionNotFoundException class.
 */
class DefinitionNotFoundException extends DefinitionException implements NotFoundExceptionInterface
{
}
