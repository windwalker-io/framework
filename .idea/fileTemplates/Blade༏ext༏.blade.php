<?php

/**
 * Global variables
 * --------------------------------------------------------------
 * @var ${DS}app           \Windwalker\Web\Application                 Global Application
 * @var ${DS}package       \Windwalker\Core\Package\AbstractPackage    Package object.
 * @var ${DS}view          \Windwalker\Data\Data                       Some information of this view.
 * @var ${DS}uri           \Windwalker\Uri\UriData                     Uri information, example: ${DS}uri->path
 * @var ${DS}datetime      \DateTime                                   PHP DateTime object of current time.
 * @var ${DS}helper        \Windwalker\Core\View\Helper\Set\HelperSet  The Windwalker HelperSet object.
 * @var ${DS}router        \Windwalker\Core\Router\PackageRouter       Router object.
 * @var ${DS}asset         \Windwalker\Core\Asset\AssetManager         The Asset manager.
 */
 
 declare(strict_types=1);

?>

@extends('_global.html')

@section('content')

@stop
