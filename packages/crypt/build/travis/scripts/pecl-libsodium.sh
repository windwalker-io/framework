#!/bin/sh

if [ $TRAVIS_PHP_VERSION = '7.2' ]; then
    pecl install libsodium;
fi
