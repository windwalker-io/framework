<?php

require_once __DIR__ . '/vendor/autoload.php';

use Windwalker\Filesystem\File;

$file = __DIR__ . '/tmp/write.txt';

$content = <<<EOF
something.
EOF;

// create file
File::write($file, $content);

echo "File path: \n";
echo File::stripExtension($file) . "\n";

echo "File extension: \n";
echo File::getExtension($file) . "\n";

echo "File name: \n";
echo File::getFilename($file) . "\n";

// delete file
File::delete($file);
