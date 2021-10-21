# vi: set sw=4 ts=4:

-include ../../path.mk
-include ../../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

###########################################################################

MYNAME	:= "Aries/dlink.2013.gui/htdocs/hnap"
FILES	:= GetInternetConnUpTime.php GetInterfaceStatistics.php GetDeviceSettings.php GetFirmwareValidation.php

###########################################################################

###########################################################################
all:

rootfs:
	@echo -e "\033[32mCreating rootfs for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/etc/templates/hnap ] || mkdir -p $(TARGET)/etc/templates/hnap

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(FILES); do \
			cp --remove-destination $$i $(TARGET)/etc/templates/hnap/$$i; \
		done

clean:

.PHONY: all rootfs install clean