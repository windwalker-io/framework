<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\IO;

/**
 * Windwalker Input JSON Class
 *
 * This class decodes a JSON string from the raw request data and makes it available via
 * the standard Input interface.
 *
 * @since  2.0
 */
class JsonInput extends FormDataInput
{
    /**
     * Prepare source.
     *
     * @param   array   $source    Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
     * @param   boolean $reference If set to true, he source in first argument will be reference.
     *
     * @return  void
     */
    public function prepareSource(&$source = null, $reference = false)
    {
        if (is_null($source)) {
            $raw = static::loadRawFromRequest();

            $this->data = json_decode($raw, true);

            if (!is_array($this->data)) {
                $this->data = [];
            }
        } else {
            if ($reference) {
                $this->data = &$source;
            } else {
                $this->data = $source;
            }
        }
    }
}
