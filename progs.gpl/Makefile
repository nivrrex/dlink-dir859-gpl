#
# Makefile for progs.gpl
#

.EXPORT_ALL_VARIABLES:

-include arch.mk

all:
	@echo -e "\033[32mBuilding progs.gpl ...\033[0m"
	@for i in $(PROGS_GPL_SUBDIRS); do make -C $$i || exit $? ; done

install:
	@echo -e "\033[32mInstalling progs.gpl ...\033[0m"
	@for i in $(PROGS_GPL_SUBDIRS); do make -C $$i install || exit $? ; done

clean:
	@echo -e "\033[32mCleaning progs.gpl\033[0m"
	@for i in $(PROGS_GPL_SUBDIRS); do make -C $$i clean || exit $? ; done

gpl:
	rm -rf telnetd
	rm -rf net-snmp/net-snmp-5.4.1/agent/mibgroup/encrypt_des.*
	mv Makefile.gpl Makefile

license: license_clean
	@echo -e "\033[32mGenerate GPL License files\033[0m"
	@for i in $(PROGS_GPL_SUBDIRS); do make -C $$i -f ../Makefile.license || exit $? ; done

license_clean:
	@echo -e "\033[32mClean GPL License files\033[0m"
	@make -f Makefile.license clean




.PHONY: all install clean gpl
