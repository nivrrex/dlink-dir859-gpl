#!/bin/sh
echo $1
xmldbc -P /etc/scripts/wps/wps_sta_state.php -V STATE=$1  > /dev/null
exit 0 
