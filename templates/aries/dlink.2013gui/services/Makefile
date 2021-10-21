# vi: set sw=4 ts=4:
#
# Makefile for services of Aries.
#

-include ../path.mk
-include ../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

###########################################################################

MYNAME	:= "Aries/dlink.2013gui/services"
SERVICES:=  ppp4_status
DIRS := INET

###########################################################################

ifeq ($(ELBOX_ARIES_FEATURE_ROUTER_GATEWAY),y)
SERVICES +=  MULTICAST OPENDNS4 OPENDNS4.MAP IPTABLES IPTDEFCHAIN \
		   DEVICE.TIME IPTPFWD DNS EMAIL LOG.EMAIL ACCESSCTRL \
		   LAN IPTOBF REBOOT DEVICE DDNS4.WAN-1 DDNS4.WAN-2 DDNS4.WAN-3 DDNS6.WAN-1 \
		   WEBACCESS DEVICE.HOSTNAME IPTFIREWALL IP6TFIREWALL IPTMACCTRL \
		   SCH_REBOOT SCH_REBOOT_ACTIVE

ifeq ($(ELBOX_TEMPLATE_ARIES_ENABLE_USER_MANAGEMENT),y)
SERVICES += DEVICE.ACCOUNT
endif

# subdirectories.
DIRS	:= HTTP DNS BWC IPTABLES LOG DHCPS INET INFSVCS DDNS
endif
#for AP or router all need mdns
SERVICES += MDNSRESPONDER

ifeq ($(ELBOX_USE_IPV6),y)
SERVICES += IP6TOBF IP6TABLES
DIRS	+= IP6TABLES
endif

ifeq ($(ELBOX_ARIES_FEATURE_BRIDGE_AP),y)
#SERVICES +=	
# subdirectories.
DIRS	:= HTTP DHCPS 
endif

ifeq ($(ELBOX_PROGS_PRIV_UPNP_AV),y)
SERVICES += UPNPAV UPNPAV.PRESCAN
endif

ifeq ($(CONFIG_SHAREPORT_WITH_SAMBA),y)
SERVICES += SHAREPORT
endif

ifeq ($(ELBOX_PROGS_GPL_ITUNES_SERVER),y)
SERVICES += ITUNES
endif


ifeq ($(ELBOX_PROGS_PRIV_ALPHA_STUNNEL),y)
SERVICES += STUNNEL
SERVICES += HTTP.WAN-1
endif

ifeq ($(ELBOX_PROGS_GPL_NETATALK),y)
SERVICES += NETATALK
endif

ifeq ($(ELBOX_PROGS_GPL_UPNPC_IGD_FORWARDER),y)
SERVICES += UPNPC
endif

ifeq ($(ELBOX_PROGS_GPL_PPTPD),y)
SERVICES += VPN
endif

ifeq ($(ELBOX_PROGS_GPL_SHAIRPORT),y)
SERVICES += SHAIRPORT
endif

ifeq ($(ELBOX_PROGS_GPL_SAMBA_3_0_24),y)
SERVICES += SAMBA
endif

###########################################################################

all:

rootfs:
	@echo -e "\033[32mCreating rootfs for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/etc/services ] || mkdir -p $(TARGET)/etc/services
	$(Q)[ -d $(TARGET)/etc/config ] || mkdir -p $(TARGET)/etc/config

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(SERVICES); do cp $$i.php $(TARGET)/etc/services/.; done
	$(Q)for i in $(DIRS); do \
		[ -d $(TARGET)/etc/services/$$i ] || mkdir -p $(TARGET)/etc/services/$$i ; \
		cp $$i/*.php $(TARGET)/etc/services/$$i/.; \
	done
ifeq ($(ELBOX_PROGS_GPL_UPNPC_IGD_FORWARDER),y)
	cp infservices_for_upnpc.php $(TARGET)/etc/services/INFSVCS/infservices.php
	cp IPTDEFCHAIN_for_upnpc.php $(TARGET)/etc/services/IPTDEFCHAIN.php
endif

ifeq ($(strip $(ELBOX_MINIDLNA)),y)
	cp  UPNPAV_minidlna.php $(TARGET)/etc/services/UPNPAV.php
endif

clean:

.PHONY: all rootfs install clean
