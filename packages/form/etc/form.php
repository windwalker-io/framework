<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

use Windwalker\Core\Form\BootstrapFormRenderer;
use Windwalker\Core\Form\FormProvider;

use function Windwalker\DI\create;

return [
    'form' => [
        'enabled' => true,

        'providers' => [
            FormProvider::class
        ],

        'bindings' => [
            //
        ],

        'extends' => [
            //
        ],

        'factories' => [
            //
        ]
    ],
];
