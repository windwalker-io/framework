<?php

declare(strict_types=1);

namespace PHPSTORM_META {

    override(
        \Windwalker\Attributes\AttributesResolver::createObject(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    // Helpers
    override(
        \Windwalker\tap(0),
        elementType(0)
    );
}
