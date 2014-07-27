<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

include __DIR__ . '/../../../../../autoload.php';

$dir = __DIR__ . '/files';

// \Windwalker\Filesystem\File::write($dir . '/aaa/bbb/ccc/ddd/ggg.txt', 'aaa');

// \Windwalker\Filesystem\Folder::move($dir . '/foo', $dir . '/aaa', true);

// \Windwalker\Filesystem\Folder::delete($dir . '/aaa');
// \Windwalker\Filesystem\File::copy($dir . '/file2.txt', $dir . '/aaa/bbb/ccc/file2.txt');

// show(\Windwalker\Filesystem\Folder::listFolderTree($dir));
echo \Windwalker\Filesystem\Path::find($dir, 'file2.txt');
