#!/bin/sh

cd src/$1;
echo "Start Test: $1";
echo '----------------------------------------------------------------------------------------';
composer update;
phpunit --configuration phpunit.travis.xml;
cd ../..;