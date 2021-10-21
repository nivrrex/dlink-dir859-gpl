#!/bin/sh
iwpriv wifig0 set ccp_debug=2
iwpriv wifia0 set ccp_debug=2
iwpriv wifig0 get_ccp_status > /var/antenna
iwpriv wifia0 get_ccp_status >> /var/antenna
sed 'N;s/\n/ /g' /var/antenna > /var/antenna_info
exit 0
