#!/bin/sh
TOOL=flash
LOADDEFSW="$TOOL default-sw"

$TOOL test-dsconf
if [ $? != 0 ]; then
	echo 'Default configuration invalid, reset default!'
	$LOADDEFSW
fi
