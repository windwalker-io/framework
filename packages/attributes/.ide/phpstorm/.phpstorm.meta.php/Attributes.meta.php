<?php

declare(strict_types=1);

namespace PHPSTORM_META {

    use Windwalker\Attributes\AttributesAccessor;

    override(
        AttributesAccessor::getFirstAttributeInstance(1),
        type(1)
    );

    override(
        \Windwalker\Attributes\AttributesResolver::createObject(0),
        type(0)
    );
}
