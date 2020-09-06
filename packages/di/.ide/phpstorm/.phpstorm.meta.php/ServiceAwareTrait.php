<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace PHPSTORM_META {

    use Windwalker\DI\ServiceAwareTrait;

    override(
        ServiceAwareTrait::make(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        ServiceAwareTrait::service(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        ServiceAwareTrait::resolve(0),
        map(
            [
                '' => '@',
            ]
        )
    );
}
