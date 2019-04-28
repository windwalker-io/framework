<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

// phpcs:disable

include_once __DIR__ . '/../../../../vendor/autoload.php';

$event = new \Windwalker\Event\Event('Aaa');

class AL
{
    public function Aaa($event)
    {
        echo 'Aaa' . "\n";
    }

    public function Bbb($event)
    {
        echo 'Bbb' . "\n";
    }
}
$d = new \Windwalker\Event\Dispatcher();

$d->addListener(new AL());
$d->addListener(
    function ($event) {
        echo 'Ccc' . "\n";
    },
    ['Ccc' => 5]
);

$d->triggerEvent($event);

$d->triggerEvent('Ccc');

