<?php

declare(strict_types=1);

namespace Windwalker {

    use Windwalker\Scalars\ArrayObject;
    use Windwalker\Scalars\StringObject;

    if (!function_exists('\Windwalker\str')) {
        /**
         * str
         *
         * @param  mixed        $string
         * @param  null|string  $encoding
         *
         * @return  StringObject
         */
        function str($string = '', ?string $encoding = StringObject::ENCODING_UTF8): StringObject
        {
            return StringObject::wrap($string, $encoding);
        }
    }

    if (!function_exists('\Windwalker\arr')) {
        /**
         * collect
         *
         * @param  array  $data
         *
         * @return  ArrayObject
         *
         * @since  3.5
         */
        function arr($data = []): ArrayObject
        {
            return new ArrayObject($data);
        }
    }
}
