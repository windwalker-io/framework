<?php

declare(strict_types=1);

namespace Windwalker\DI\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * The DefinitionException class.
 */
class DefinitionException extends Exception implements ContainerExceptionInterface
{
}
