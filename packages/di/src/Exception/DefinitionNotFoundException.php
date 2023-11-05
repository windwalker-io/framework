<?php

declare(strict_types=1);

namespace Windwalker\DI\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * The DefinitionNotFoundException class.
 */
class DefinitionNotFoundException extends DefinitionException implements NotFoundExceptionInterface
{
}
