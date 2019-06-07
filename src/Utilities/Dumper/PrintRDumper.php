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

        switch ($type) {
            case 'double':
                switch (true) {
                    case INF === $value:
                        $value = '(INF)';
                        break;
                    case -INF === $value:
                        $value = '(-INF)';
                        break;
                    case is_nan($value):
                        $value = '(NAN)';
                        break;
                    default:
                        $value = (string) $value;

                        if (false === strpos($value, $this->decimalPoint)) {
                            $value .= $this->decimalPoint . '0';
                        }
                        break;
                }
                break;

            case 'NULL':
                $value = '(NULL)';
                break;

            case 'boolean':
                $value = $value ? '(TRUE)' : '(FALSE)';
                break;
        }

        $this->line .= $value;

        $this->dumpLine($cursor->depth);
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
            $prefix = 'Resource ' . $class;
        } else {
            $prefix = 'Array';
        }

        $this->line .= $prefix;

        $this->dumpLine($cursor->depth);

        $this->line .= '(';

        $depth = $cursor->depth;

        if ($cursor->depth === 1) {
            $depth = $cursor->depth + 1;
        } elseif ($cursor->depth >= 2) {
            $depth = $cursor->depth + $cursor->depth;
        }

        $this->dumpLine($depth);
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
        if (!$hasChild && $cut > 0) {
            $this->line .= '*MAX LEVEL*';
            $this->dumpLine($cursor->depth + 2);
        }

        $this->line .= ")\n";

        $depth = $cursor->depth;

        if ($cursor->depth === 1) {
            $depth = $cursor->depth + 1;
        } elseif ($cursor->depth >= 2) {
            $depth = $cursor->depth + $cursor->depth;
        }

        $this->dumpLine($depth);
    }

    /**
     * dumpKey
     *
     * @param Cursor $cursor
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function dumpKey(Cursor $cursor): void
    {
        if ($cursor->depth > 0) {
            $this->line .= $cursor->depth > 1 ? str_repeat('    ', $cursor->depth - 1) : '';

            $key = $cursor->hashKey;

            if (strpos($key, "\0") === 0) {
                $key = explode("\0", substr($key, 1), 2);

                if ($key[0][0] === '*') {
                    $key = $key[1] . ':protected';
                } elseif ($key[0][0] === '~') {
                    $key = $key[1] . ':private';
                } else {
                    $key = $key[1] . ':private';
                }
            }

            $this->line .= '[' . $key . '] => ';
        }
    }
}
