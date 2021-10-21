#!/bin/sh
echo [$0]: $1 ... > /dev/console                                                
if [ "$1" = "start" ]; then
	xmldbc -P /htdocs/phplib/isplst_all.php 
	xmldbc -P /htdocs/phplib/isplst_choose.php 
	xmldbc -X runtime/services/operator_all
fi
