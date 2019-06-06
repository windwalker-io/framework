<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    __LICENSE__
 */

namespace Windwalker\Utilities\Dumper;

use Symfony\Component\VarDumper\Cloner\Cursor;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;

/**
 * The PrintRDumper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class PrintRDumper extends AbstractDumper
{
    /**
     * Dumps a scalar value.
     *
     * @param Cursor                $cursor The Cursor position in the dump
     * @param string                $type   The PHP type of the value being dumped
     * @param string|int|float|bool $value  The scalar value being dumped
     */
    public function dumpScalar(Cursor $cursor, $type, $value)
    {
        $this->dumpKey($cursor);

        $this->line .= $value;
    }

    /**
     * Dumps a string.
     *
     * @param Cursor $cursor The Cursor position in the dump
     * @param string $str    The string being dumped
     * @param bool   $bin    Whether $str is UTF-8 or binary encoded
     * @param int    $cut    The number of characters $str has been cut by
     */
    public function dumpString(Cursor $cursor, $str, $bin, $cut)
    {
        $this->dumpKey($cursor);

        $this->line .= $str;

        $this->dumpLine($cursor->depth);
    }

    /**
     * Dumps while entering an hash.
     *
     * @param Cursor $cursor   The Cursor position in the dump
     * @param int    $type     A Cursor::HASH_* const for the type of hash
     * @param string $class    The object class, resource type or array count
     * @param bool   $hasChild When the dump of the hash has child item
     */
    public function enterHash(Cursor $cursor, $type, $class, $hasChild)
    {
        $this->dumpKey($cursor);

        if (Cursor::HASH_OBJECT === $type) {
            $prefix = $class . ' Object';
        } elseif (Cursor::HASH_RESOURCE === $type) {
            $prefix = 'resource';
        } else {
            $prefix = 'Array';
        }

        $this->line .= $prefix;

        if ($hasChild) {
            $this->dumpLine($cursor->depth);

            $this->line .= '    (';

            $this->dumpLine($cursor->depth);
        }
    }

    /**
     * Dumps while leaving an hash.
     *
     * @param Cursor $cursor   The Cursor position in the dump
     * @param int    $type     A Cursor::HASH_* const for the type of hash
     * @param string $class    The object class, resource type or array count
     * @param bool   $hasChild When the dump of the hash has child item
     * @param int    $cut      The number of items the hash has been cut by
     */
    public function leaveHash(Cursor $cursor, $type, $class, $hasChild, $cut)
    {
        $this->line .= "\n)";

        $this->dumpLine($cursor->depth);
    }

    protected function dumpKey(Cursor $cursor)
    {
        if ($cursor->depth > 0) {
            $this->line .= '[' . $cursor->hashKey . '] => ';
        }
    }
}
