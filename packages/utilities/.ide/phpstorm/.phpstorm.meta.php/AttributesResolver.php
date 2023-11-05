<?php

declare(strict_types=1);

namespace PHPSTORM_META {

    use Windwalker\Attributes\AttributesResolver;

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
