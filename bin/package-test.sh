#!/bin/bash
BASEDIR=$(dirname "$0")
composer update --ignore-platform-reqs --prefer-stable
php vendor/bin/phpunit --configuration phpunit.travis.xml
rm -rf vendor
rm composer.lock
