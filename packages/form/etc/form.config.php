<?php

declare(strict_types=1);

namespace App\Config;

use Windwalker\Core\Attributes\ConfigModule;
use Windwalker\Core\Form\FormProvider;
use Windwalker\Form\FormPackage;

return #[ConfigModule(name: 'form', enabled: true, priority: 100, belongsTo: FormPackage::class)]
static fn() => [

    'providers' => [
        FormProvider::class,
    ],

    'bindings' => [
        //
    ],

    'extends' => [
        //
    ],

    'factories' => [
        //
    ],
];
