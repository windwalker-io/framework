<?php

require_once __DIR__ . '/vendor/autoload.php';

use Windwalker\Filesystem\File;

$uploadPath = __DIR__ . '/tmp';

if (isset($_FILES['upload']))
{
    $src = $_FILES['upload']['tmp_name'];
    $uploadFile = $uploadPath . "/" . $_FILES['upload']['name'];

    File::upload($src, $uploadPath);
}

?>
<form action="#" method="post" enctype="multipart/form-data">
    <input type="file" name="upload" />
    <button type="submit">submit</button>
</form>
