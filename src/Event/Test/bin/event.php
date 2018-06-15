<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

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

