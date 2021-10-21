#!/bin/sh
echo "[$0]: $#($@)" > /dev/console

exec_helper="/etc/scripts/tr069/tr069_host.php"
exec_cmd="xmldbc -P $exec_helper"

$exec_cmd -V CMD="$1" -V DEVICE="$2" -V TR069_CPE="$3" -V OTHER_PARAMS="$4"
exit 0