<?php

declare(strict_types=1);

namespace PHPSTORM_META {

    override(
        \Windwalker\ORM\ORM::hydrateEntity(0),
        type(0)
    );

    override(
        \Windwalker\ORM\ORM::createEntity(0),
        type(0)
    );

    override(
        \Windwalker\ORM\ORM::findOne(0),
        type(0)
    );

    override(
        \Windwalker\ORM\ORM::findOne(2),
        type(2)
    );

    override(
        \Windwalker\ORM\ORM::mustFindOne(0),
        type(0)
    );

    override(
        \Windwalker\ORM\ORM::mustFindOne(2),
        type(2)
    );

    override(
        \Windwalker\ORM\ORM::createOne(0),
        type(0)
    );

    override(
        \Windwalker\ORM\ORM::findOneOrCreate(0),
        type(0)
    );

    override(
        \Windwalker\ORM\SelectorQuery::get(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    override(
        \Windwalker\ORM\SelectorQuery::all(0),
        map(
            [
                '' => '@',
            ]
        )
    );

    // Compares
    registerArgumentsSet(
        'compare_operators',
        '=',
        '!=',
        '<',
        '<=',
        '>',
        '>=',
        'between',
        'not between',
        'in',
        'not in',
        'is',
        'is not',
    );

    expectedArguments(
        \Windwalker\ORM\SelectorQuery::where(),
        1,
        argumentsSet('compare_operators')
    );

    expectedArguments(
        \Windwalker\ORM\SelectorQuery::having(),
        1,
        argumentsSet('compare_operators')
    );
}
