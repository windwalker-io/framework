<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\String;

use Windwalker\Utilities\Classes\ImmutableHelperTrait;
use Windwalker\Utilities\Classes\StringableInterface;

/**
 * The StringObject class.
 *
 * @method StringObject getChar(int $pos)
 * @method StringObject between(string $start, string $end, int $offset = 0)
 * @method StringObject collapseWhitespaces()
 * @method bool         contains(string $search, bool $caseSensitive = true)
 * @method bool         endsWith(string $search, bool $caseSensitive = true)
 * @method bool         startsWith(string $target, bool $caseSensitive = true)
 * @method StringObject ensureLeft(string $search)
 * @method StringObject ensureRight(string $search)
 * @method bool         hasLowerCase()
 * @method bool         hasUpperCase()
 * @method StringObject match(string $pattern, string $option = 'msr')
 * @method StringObject insert(string $insert, int $position)
 * @method bool         isLowerCase()
 * @method bool         isUpperCase()
 * @method StringObject first(int $length = 1)
 * @method StringObject last(int $length = 1)
 * @method StringObject intersectLeft(string $string2)
 * @method StringObject intersectRight(string $string2)
 * @method StringObject intersect(string $string2)
 * @method StringObject pad(int $length = 0, string $substring = ' ')
 * @method StringObject padLeft(int $length = 0, string $substring = ' ')
 * @method StringObject padRight(int $length = 0, string $substring = ' ')
 * @method StringObject removeChar(int $offset, int $length = null)
 * @method StringObject removeLeft(string $search)
 * @method StringObject removeRight(string $search)
 * @method StringObject slice(int $start, int $end = null)
 * @method StringObject substring(int $start, int $end = null)
 * @method StringObject surround($substring = ['"', '"'])
 * @method StringObject toggleCase()
 * @method StringObject truncate(int $length, string $suffix = '', bool $wordBreak = true)
 * @method StringObject map(callable $callback)
 * @method StringObject filter(callable $callback)
 * @method StringObject reject(callable $callback)
 *
 * @since  3.2
 */
class StringObject implements \Countable, \ArrayAccess, \IteratorAggregate, StringableInterface
{
    use ImmutableHelperTrait;

    /**
     * We only provides 3 default encoding constants of PHP.
     *
     * @see http://php.net/manual/en/xml.encoding.php
     */
    const ENCODING_DEFAULT_ISO = 'ISO-8859-1';

    const ENCODING_UTF8 = 'UTF-8';

    const ENCODING_US_ASCII = 'US-ASCII';

    /**
     * Property string.
     *
     * @var  string
     */
    protected $string = '';

    /**
     * Property encoding.
     *
     * @var  string
     */
    protected $encoding = null;

    /**
     * create
     *
     * @param string      $string
     * @param null|string $encoding
     *
     * @return  static
     */
    public static function create($string = '', $encoding = self::ENCODING_UTF8)
    {
        return new static($string, $encoding);
    }

    /**
     * fromArray
     *
     * @param array       $strings
     * @param null|string $encoding
     *
     * @return  static[]
     */
    public static function fromArray(array $strings, $encoding = self::ENCODING_UTF8)
    {
        foreach ($strings as $k => $string) {
            $strings[$k] = static::create($string, $encoding);
        }

        return $strings;
    }

    /**
     * StringObject constructor.
     *
     * @see  http://php.net/manual/en/mbstring.supported-encodings.php
     *
     * @param string      $string
     * @param null|string $encoding
     */
    public function __construct($string = '', $encoding = self::ENCODING_UTF8)
    {
        $this->string = $string;
        $this->encoding = $encoding === null ? static::ENCODING_UTF8 : $encoding;
    }

    /**
     * __call
     *
     * @param string $name
     * @param array  $args
     *
     * @return  mixed
     * @throws \ReflectionException
     */
    public function __call($name, array $args)
    {
        $class = Str::class;

        if (is_callable([$class, $name])) {
            return $this->callProxy($class, $name, $args);
        }

        throw new \BadMethodCallException(sprintf('Call to undefined method: %s::%s()', get_called_class(), $name));
    }

    /**
     * callProxy
     *
     * @param string $class
     * @param string $method
     * @param array  $args
     *
     * @return  static
     * @throws \ReflectionException
     */
    protected function callProxy($class, $method, array $args)
    {
        $new = $this->cloneInstance();

        $ref = new \ReflectionMethod($class, $method);
        $params = $ref->getParameters();
        array_shift($params);

        /** @var \ReflectionParameter $param */
        foreach (array_values($params) as $k => $param) {
            if (!array_key_exists($k, $args)) {
                if ($param->getName() === 'encoding' && !isset($args[$k])) {
                    $args[$k] = $this->encoding;
                    continue;
                }

                $args[$k] = $param->getDefaultValue();
            }
        }

        array_unshift($args, $new->string);

        $result = call_user_func_array([$class, $method], $args);

        if (is_string($result)) {
            $new->string = $result;

            return $new;
        }

        return $result;
    }

    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     *        <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->chop());
    }

    /**
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        $offset = $offset >= 0 ? $offset : (int) abs($offset) - 1;

        return $this->length() > $offset;
    }

    /**
     * Offset to retrieve
     *
     * @param int $offset The offset to retrieve.
     *
     * @return string Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getChar($offset);
    }

    /**
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $string <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $string)
    {
        $this->string = Mbstring::substrReplace($this->string, $string, $offset, 1, $this->encoding);
    }

    /**
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        if ($this->length() < abs($offset)) {
            return;
        }

        $this->string = Str::removeChar($this->string, $offset, 1, $this->encoding);
    }

    /**
     * Count elements of an object
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *        </p>
     *        <p>
     *        The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->length();
    }

    /**
     * Magic method to convert this object to string.
     *
     * @return  string
     */
    public function __toString()
    {
        return (string) $this->string;
    }

    /**
     * Method to get property Encoding
     *
     * @return  string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Method to set property encoding
     *
     * @param   string $encoding
     *
     * @return  static  Return self to support chaining.
     */
    public function withEncoding($encoding)
    {
        return $this->cloneInstance(
            function (StringObject $new) use ($encoding) {
                $new->encoding = $encoding;
            }
        );
    }

    /**
     * Method to get property String
     *
     * @return  string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Method to set property string
     *
     * @param   string $string
     *
     * @return  static  Return self to support chaining.
     */
    public function withString($string)
    {
        return $this->cloneInstance(
            function (StringObject $new) use ($string) {
                $new->string = $string;
            }
        );
    }

    /**
     * toLowerCase
     *
     * @return  static
     */
    public function toLowerCase()
    {
        $new = $this->cloneInstance();

        $new->string = Mbstring::strtolower($new->string, $new->encoding);

        return $new;
    }

    /**
     * toUpperCase
     *
     * @return  static
     */
    public function toUpperCase()
    {
        return $this->cloneInstance(
            function (StringObject $new) {
                $new->string = Mbstring::strtoupper($new->string, $new->encoding);
            }
        );
    }

    /**
     * length
     *
     * @return  int
     */
    public function length()
    {
        return Mbstring::strlen($this->string, $this->encoding);
    }

    /**
     * split
     *
     * @param int $length
     *
     * @return  array|bool
     */
    public function chop($length = 1)
    {
        return Mbstring::strSplit($this->string, $length, $this->encoding);
    }

    /**
     * replace
     *
     * @param array|string $search
     * @param array|string $replacement
     * @param int|null     $count
     *
     * @return  static
     */
    public function replace($search, $replacement, &$count = null)
    {
        return $this->cloneInstance(
            function (StringObject $new) use ($search, $replacement, &$count) {
                $new->string = str_replace($search, $replacement, $new->string, $count);
            }
        );
    }

    /**
     * compare
     *
     * @param string $compare
     * @param bool   $caseSensitive
     *
     * @return  int
     */
    public function compare($compare, $caseSensitive = true)
    {
        if ($caseSensitive) {
            return Mbstring::strcmp($this->string, $compare);
        }

        return Mbstring::strcasecmp($this->string, $compare, $this->encoding);
    }

    /**
     * reverse
     *
     * @return  static
     */
    public function reverse()
    {
        return $this->cloneInstance(
            function (StringObject $new) {
                $new->string = Mbstring::strrev($new->string);
            }
        );
    }

    /**
     * substrReplace
     *
     * @param string $replace
     * @param int    $start
     * @param int    $offset
     *
     * @return  static
     */
    public function substrReplace($replace, $start, $offset = null)
    {
        return $this->cloneInstance(
            function (StringObject $new) use ($replace, $start, $offset) {
                $new->string = Mbstring::substrReplace($new->string, $replace, $start, $offset, $this->encoding);
            }
        );
    }

    /**
     * ltrim
     *
     * @param string|null $charlist
     *
     * @return  static
     */
    public function trimLeft($charlist = null)
    {
        return $this->cloneInstance(
            function (StringObject $new) use ($charlist) {
                $new->string = Mbstring::ltrim($new->string, $charlist);
            }
        );
    }

    /**
     * rtrim
     *
     * @param string|null $charlist
     *
     * @return  static
     */
    public function trimRight($charlist = null)
    {
        return $this->cloneInstance(
            function (StringObject $new) use ($charlist) {
                $new->string = Mbstring::rtrim($new->string, $charlist);
            }
        );
    }

    /**
     * trim
     *
     * @param string|null $charlist
     *
     * @return  static
     */
    public function trim($charlist = null)
    {
        return $this->cloneInstance(
            function (StringObject $new) use ($charlist) {
                $new->string = Mbstring::trim($new->string, $charlist);
            }
        );
    }

    /**
     * ucfirst
     *
     * @return  static
     */
    public function upperCaseFirst()
    {
        return $this->cloneInstance(
            function (StringObject $new) {
                $new->string = Mbstring::ucfirst($new->string, $this->encoding);
            }
        );
    }

    /**
     * lcfirst
     *
     * @return  static
     */
    public function lowerCaseFirst()
    {
        return $this->cloneInstance(
            function (StringObject $new) {
                $new->string = Mbstring::lcfirst($new->string, $this->encoding);
            }
        );
    }

    /**
     * upperCaseWords
     *
     * @return  static
     */
    public function upperCaseWords()
    {
        return $this->cloneInstance(
            function (StringObject $new) {
                $new->string = Mbstring::ucwords($new->string, $this->encoding);
            }
        );
    }

    /**
     * substrCount
     *
     * @param string $search
     * @param bool   $caseSensitive
     *
     * @return  int
     */
    public function substrCount($search, $caseSensitive = true)
    {
        return Mbstring::substrCount($this->string, $search, $caseSensitive, $this->encoding);
    }

    /**
     * indexOf
     *
     * @param string $search
     *
     * @return  int|bool
     */
    public function indexOf($search)
    {
        return Mbstring::strpos($this->string, $search, 0, $this->encoding);
    }

    /**
     * indexOf
     *
     * @param string $search
     *
     * @return  int|bool
     */
    public function indexOfLast($search)
    {
        return Mbstring::strrpos($this->string, $search, 0, $this->encoding);
    }

    /**
     * explode
     *
     * @param string   $delimiter
     * @param int|null $limit
     *
     * @return  array
     */
    public function explode($delimiter, $limit = null)
    {
        // Fix HHVM default explode limit issue
        // @see  https://github.com/facebook/hhvm/issues/7696
        // @see  https://3v4l.org/fllad
        if ($limit === null) {
            $limit = defined('HHVM_VERSION') ? 0x7FFFFFFF : PHP_INT_MAX;
        }

        return explode($delimiter, $this->string, $limit);
    }

    /**
     * apply
     *
     * @param callable $callback
     *
     * @return  static
     */
    public function apply(callable $callback)
    {
        return $this->cloneInstance(
            function ($new) use ($callback) {
                $new->string = $callback($new->string);
            }
        );
    }
}
