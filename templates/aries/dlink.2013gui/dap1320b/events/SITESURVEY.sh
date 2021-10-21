#!/bin/sh
iwpriv ra0 set SiteSurvey=
iwpriv ra0 get_site_survey > /var/ssvy.txt
Parse2DB sitesurvey -i 24G -f /var/ssvy.txt -d > /dev/null
rm /var/ssvy.txt
exit 0
