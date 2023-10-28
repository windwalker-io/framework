<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Helper;

use Windwalker\Http\File\HttpUploadFileInterface;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\TypeCast;

use function Windwalker\uid;

/**
 * The MultipartHelper class.
 */
class MultipartHelper
{
    /**
     * @param  string  $boundary
     * @param  array   $data
     *
     * @return  string
     *
     * @throws \Exception
     */
    public static function toFormData(string $boundary, array $data): string
    {
        $eol = "\r\n";
        $data = static::flattenToQueryName($data);
        $postdata = [];

        foreach ($data as $key => $val) {
            if ($val instanceof HttpUploadFileInterface) {
                $fileContents = $val->getContent();

                $item = sprintf(
                    "Content-Disposition: form-data; name=\"%s\"; filename=\"%s\"$eol",
                    $key,
                    $val->getPostFilename() ?: uid() . '.txt'
                );

                $mime = $val->getMimeType() ?: 'text/plain';
                $item .= "Content-Type: {$mime}$eol";

                $item .= "Content-Transfer-Encoding: binary{$eol}{$eol}";
                $item .= $fileContents . $eol;

                $postdata[] = $item;
            } else {
                $postdata[] = "Content-Disposition: form-data; name=\"" . $key . "\"{$eol}{$eol}" . $val;
            }
        }

        $postdata = implode("{$eol}--{$boundary}{$eol}", $postdata);

        return "--$boundary{$eol}$postdata{$eol}--$boundary--";
    }

    public static function flattenToQueryName(
        array $array,
        ?string $prefix = null
    ): array {
        $temp = [];

        foreach (TypeCast::toArray($array, false) as $k => $v) {
            $key = $prefix !== null ? $prefix . '[' . $k . ']' : $k;

            if (is_array($v)) {
                $temp[] = static::flattenToQueryName($v, (string) $key);
            } else {
                $temp[] = [$key => $v];
            }
        }

        // Prevent resource-greedy loop.
        // @see https://github.com/dseguy/clearPHP/blob/master/rules/no-array_merge-in-loop.md
        if (count($temp)) {
            return array_merge(...$temp);
        }

        return [];
    }

    /**
     * @return  string
     *
     * @throws \Exception
     */
    public static function createBoundary(): string
    {
        $boundary = base64_encode(random_bytes(16));

        return '----' . rtrim(strtr($boundary, '+/', '-_'), '=');
    }

    /**
     * parseFormData
     *
     * @param  string  $input
     *
     * @return array
     *
     * @link  http://stackoverflow.com/questions/5483851/manually-parse-raw-http-data-with-php/5488449#5488449
     */
    public static function parseFormData(string $input): array
    {
        $boundary = substr($input, 0, strpos($input, "\r\n"));

        // Fetch each part
        $parts = array_slice(explode($boundary, $input), 1);
        $data = [];
        $files = [];

        foreach ($parts as $part) {
            // If this is the last part, break
            if (str_starts_with($part, '--')) {
                break;
            }

            // Separate content from headers
            $part = ltrim($part, "\r\n");

            [$rawHeaders, $content] = explode("\r\n\r\n", $part, 2);

            $content = substr($content, 0, strlen($content) - 2);

            // Parse the headers list
            $rawHeaders = explode("\r\n", $rawHeaders);
            $headers = [];

            foreach ($rawHeaders as $header) {
                [$name, $value] = explode(':', $header, 2);

                $headers[strtolower($name)] = ltrim($value, ' ');
            }

            // Parse the Content-Disposition to get the field name, etc.
            if (isset($headers['content-disposition'])) {
                preg_match(
                    '/^form-data; *name="([^"]+)"(?:; *filename="([^"]+)")?/',
                    $headers['content-disposition'],
                    $matches
                );

                $field = $matches[1];
                $fileName = ($matches[2] ?? null);

                $fieldName = str_replace(['[', '][', ']'], ['.', '.', ''], $field);

                // If we have no filename, save the data. Otherwise, save the file.
                if ($fileName === null) {
                    Arr::set($data, $fieldName, $content);
                } else {
                    $tempFile = tempnam(sys_get_temp_dir(), 'sfy');

                    file_put_contents($tempFile, $content);

                    $content = [
                        'name' => $fileName,
                        'type' => $headers['content-type'],
                        'tmp_name' => $tempFile,
                        'error' => 0,
                        'size' => filesize($tempFile),
                    ];

                    Arr::set($files, $fieldName, $content);

                    register_shutdown_function(
                        static function () use ($tempFile) {
                            @unlink($tempFile);
                        }
                    );
                }
            }
        }

        return [
            'data' => $data,
            'files' => $files,
        ];
    }
}
