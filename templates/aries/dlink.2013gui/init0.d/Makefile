# vi: set sw=4 ts=4:

-include ../path.mk
-include ../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

###########################################################################

MYNAME  := "Aries/dlink.2013gui/scripts"
ifeq ($(strip $(CONFIG_ENCRYPT_FW_ENABLE)),y)
FILES	:= S91proclink.sh S41autowan.sh S93cpuload.sh S50m2u.sh S41factorydefault.sh S40ttyevent.sh S80mfcd.sh S24sealpac.sh S44schreboot.sh
else
FILES	:= S91proclink.sh S41autowan.sh S93cpuload.sh S50m2u.sh S41factorydefault.sh S40ttyevent.sh S80telnetd.sh S24sealpac.sh S44schreboot.sh
endif

ifeq ($(ELBOX_PROGS_GPL_PPTPD),y)
	FILES += S94vpn.sh
endif

###########################################################################

all:

rootfs:
	@echo -e "\033[32mCreating rootfs for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/etc/scripts ] || mkdir -p $(TARGET)/etc/scripts

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(FILES); do \
		cp --remove-destination $$i $(TARGET)/etc/init0.d/.; \
		chmod +x $(TARGET)/etc/init0.d/$$i; \
	done

clean:
.PHONY: all rootfs install clean
