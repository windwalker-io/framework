<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace PHPSTORM_META {

    use Windwalker\Utilities\Attributes\AttributesResolver;

    override(
        AttributesResolver::createObject(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        AttributesResolver::decorateObject(0),
        map(
            [
                '' => '@',
            ]
        )
    );
}
