#!/bin/sh

cd src/$1;
composer update;
phpunit --configuration phpunit.travis.xml;
cd ../..;