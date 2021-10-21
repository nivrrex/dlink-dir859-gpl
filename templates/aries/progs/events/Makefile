# vi: set sw=4 ts=4:

-include ../path.mk
-include ../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

###########################################################################

MYNAME	:= "Aries/events"
FILES	:=	SEALPAC-SAVE.sh SEALPAC-LOAD.sh SEALPAC-CLEAR.sh SEALPAC.php \
			FWUPDATER.sh CFGUPDATER.sh freset.sh reboot.sh SENDMAIL.php 

FILES	+=	DHCP4-RENEW.sh DHCP4-RELEASE.sh
FILES	+=	UPDATELEASES.sh UPDATELEASES.php UPDATERESOLV.sh UPDATERESOLV.php

#using usb3gkit to check we have 3g dingle support
ifeq ($(CONFIG_RGBIN_USB3G_KIT),y)
FILES	+=	DISABLE.3G.sh DISABLE.3G.php
FILES	+=	ENABLE.3G.sh ENABLE.3G.php DIALINIT.3G.sh sim_chk.php sim_chk.sh ttyplugin.sh ttyplugoff.sh
endif

FILES	+=	DHCPS-RESTART.sh DHCPS-RESTART.php
FILES	+=	WAN-DETECT.sh WAN-DETECT.php WAN-1_dhcp_cb.sh
FILES	+=	WAN_dhcp_chk.sh WAN_dhcp_chk.php
FILES	+=	WAN_dhcp_pri.sh WAN_dhcp_pri.php
FILES	+=	dhcp_pri_chk.sh dhcp_pri_chk.php
FILES	+=	WAN_ppp_dis.sh WAN_ppp_dis.php option.discover.php
FILES	+=	AUTOCONFIG.php AUTOCONFIG.sh
FILES	+=	DHCPS-REDETECT.sh DHCPS-REDETECT.php
FILES	+=	INF-RESTART.php
FILES   +=  resetcfg-wifi.sh RESETCFG.WIFI.php
###########################################################################

ifeq ($(ELBOX_ARIES_FEATURE_ROUTER_GATEWAY),y)
FILES	+=	DNSCACHE-FLUSH.sh DNSCACHE-FLUSH.php
endif
ifeq ($(ELBOX_ARIES_FEATURE_BRIDGE_AP),y)
FILES	+=
endif
ifeq ($(ELBOX_PROGS_PRIV_SMART404),y)
FILES	+=	update_smart404.php
endif
ifeq ($(ELBOX_TEMPLATE_ARIES_STORAGE_SUPPORT),y)
FILES	+=	FORMAT.php MOUNT.ALL.php disk.sh
endif

ifeq ($(ELBOX_PROGS_PRIV_UPNP_AV),y)
FILES += upnpav_refresh.sh
endif

ifeq ($(ELBOX_USE_IPV6),y)
FILES += WANV6_ppp_dis.sh WANV6_ppp_dis.php WANV6_ppp_chk.sh WANV6_ppp_chk.php option6.discover.php autodetect.php autodetect-revert.php
endif
###########################################################################
all:

rootfs:
	@echo -e "\033[32mCreating rootfs for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/etc/events ] || mkdir -p $(TARGET)/etc/events

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(FILES); do \
			cp --remove-destination $$i $(TARGET)/etc/events/$$i; \
			chmod +x $(TARGET)/etc/events/$$i; \
		done

clean:

.PHONY: all rootfs install clean
