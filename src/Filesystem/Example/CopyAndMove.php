<?php

require_once __DIR__ . '/vendor/autoload.php';

use Windwalker\Filesystem\File;

$file = __DIR__ . '/tmp/write.txt';
$fileMovePath = __DIR__ . '/tmp/move.txt';
$fileCopyPath = __DIR__ . '/tmp/copy.txt';

$content = <<<EOF
something.
EOF;

// create file
File::write($file, $content);

// Move
File::move($file, $fileMovePath);

// create file
File::write($file, $content);

// Force move if file exsits
File::move($file, $fileMovePath, true);

// create file
File::write($file, $content);

// Copy
File::copy($file, $fileCopyPath);

// Force copy if file exists
File::copy($file, $fileCopyPath, true);

// delete file
File::delete($file);
File::delete($fileCopyPath);
File::delete($fileMovePath);
