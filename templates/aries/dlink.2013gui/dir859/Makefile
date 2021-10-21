.EXPORT_ALL_VARIABLES:
# vi: set sw=4 ts=4:

-include ../path.mk
-include ../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

#####################################################################

MYMAKE	:= make V=$(V) DEBUG=$(DEBUG)
#SUBDIRS := defnodes  events  htdocs  rc  scripts  services  udev.rules  wifi hnap
SUBDIRS := defnodes  events  htdocs  rc  scripts  services wifi hnap
LANFILES :=
ifneq ($(strip $(ELBOX_CREATE_LANGUAGE_PACK)),y)
#SUBDIRS += locale
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_HOSTAPD_WPS2)),y)
SUBDIRS += wifi_pmf
else
SUBDIRS += wifi
endif

all:
	$(Q)for i in $(SUBDIRS); do \
	if [ -f $$i/Makefile ]; then \
		$(MYMAKE) -C $$i all; \
	fi \
	done

rootfs:
	$(Q)for i in $(SUBDIRS); do \
	if [ -f $$i/Makefile ]; then \
		$(MYMAKE) -C $$i rootfs; \
	fi \
	done

install:
	$(Q)for i in $(SUBDIRS); do \
	if [ -f $$i/Makefile ]; then \
		$(MYMAKE) -C $$i install; \
	else \
		[ -d $(TARGET)/$$i ] || mkdir -p $(TARGET)/$$i ;\
		cp -r --remove-destination $$i $(TARGET)/$$i/. ;\
	fi \
	done

	$(Q)for i in $(LANGDIRS); do \
	[ -d $(TARGET)/etc/sealpac ] || mkdir -p $(TARGET)/etc/sealpac ;\
	cp -r --remove-destination $$i $(TARGET)/etc/sealpac/$$i ;\
	done
	
clean:
	$(Q)for i in $(SUBDIRS); do \
	if [ -f $$i/Makefile ]; then \
		$(MYMAKE) -C $$i all; \
	fi \
	done

.PHONY: all rootfs install clean
