<?php

declare(strict_types=1);

use Asika\BetterUnits\FileSize;
use Windwalker\Http\HttpClient;
use Windwalker\Http\HttpClientOptions;
use Windwalker\Http\Transport\Options\CurlOptions;
use Windwalker\Http\Transport\ProgressEvent;

include __DIR__ . '/../../../vendor/autoload.php';

$fileUrl = 'https://getsamplefiles.com/download/zip/sample-3.zip';

$http = new HttpClient();
$http->get(
    $fileUrl,
    new HttpClientOptions(
        progress: function (ProgressEvent $event) {
            show($event);
            $now = $event->downloadedFileSize->format(unit: FileSize::UNIT_KIBIBYTES);
            $total = $event->downloadTotalFileSize->format(unit: FileSize::UNIT_KIBIBYTES);

            echo "Progress: $now / $total\n";
        },
    )
);
