<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI {
    use Windwalker\DI\Definition\DefinitionFactory;
    use Windwalker\DI\Definition\DefinitionInterface;
    use Windwalker\DI\Definition\ProtectedDefinition;

    if (function_exists('protect')) {
        function protect($value): ProtectedDefinition
        {
            return new ProtectedDefinition(factory($value));
        }
    }

    if (function_exists('factory')) {
        function factory($value): DefinitionInterface
        {
            return DefinitionFactory::create($value);
        }
    }
}
