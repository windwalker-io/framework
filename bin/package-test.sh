#!/bin/bash
BASEDIR=$(dirname "$0")
$BASEDIR/composer update --ignore-platform-reqs --prefer-stable
php8 vendor/bin/phpunit --configuration phpunit.travis.xml
rm -rf vendor
rm composer.lock
