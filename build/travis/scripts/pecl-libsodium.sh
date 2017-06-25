#!/bin/sh

if [ $TRAVIS_PHP_VERSION != hhvm ]; then
    pecl install libsodium;
fi
