#!/bin/sh
iwcontrol wlan0-vxd
echo 1 > /var/wps_start_pbc
wscd -sig_pbc wlan0-vxd
