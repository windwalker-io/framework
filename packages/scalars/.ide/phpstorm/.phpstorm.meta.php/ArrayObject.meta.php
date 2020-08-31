<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace PHPSTORM_META {

    use Windwalker\Scalars\ArrayObject;

    // ArrayObject
    override(
        ArrayObject::as(0),
        map(
            [
                '' => '@',
            ]
        )
    );
}
