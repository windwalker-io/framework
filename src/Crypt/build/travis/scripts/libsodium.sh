#!/bin/sh

sudo add-apt-repository ppa:chris-lea/libsodium -y;
sudo chmod 777 /etc/apt/sources.list;
sudo echo "deb http://ppa.launchpad.net/chris-lea/libsodium/ubuntu trusty main" >> /etc/apt/sources.list;
sudo echo "deb-src http://ppa.launchpad.net/chris-lea/libsodium/ubuntu trusty main" >> /etc/apt/sources.list;
sudo apt-get update -q && sudo apt-get install libsodium-dev -y;
