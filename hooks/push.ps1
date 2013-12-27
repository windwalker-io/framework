#!/bin/bash

$COM='flower'

$ADMIN="../../../administrator/components/com_$COM"

rm -r $ADMIN
mkdir -p $ADMIN
cp -r ../Template/admin/* $ADMIN

$SITE="../../../components/com_$COM"

rm -r $SITE
mkdir -p $SITE
cp -r ../Template/site $SITE

