<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form\Field;

use Windwalker\Html\Option;

/**
 * The TimezoneField class.
 *
 * @since  2.0
 */
class TimezoneField extends ListField
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected $type = 'timezone';

    /**
     * prepareOptions
     *
     * @return  array
     */
    protected function prepareOptions()
    {
        $zones = [];

        foreach (\DateTimeZone::listIdentifiers() as $zone) {
            $pos = explode('/', $zone);

            if (count($pos) == 2) {
                $state = $pos[0];
                $city = $pos[1];
            } else {
                $state = $pos[0];
                $city = $pos[0];
            }

            if (!isset($zones[$state])) {
                $zones[$state] = [];
            }

            $zones[$state][] = new Option($city, $zone);
        }

        return $zones;
    }
}
