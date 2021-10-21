# vi: set sw=4 ts=4:

-include ../../path.mk
-include ../../arch.mk
-include $(TOPDIR)/.config
-include $(TOPDIR)/Vars.mk

MYNAME	:= "TR069"
MYMAKE	:= make V=$(V) DEBUG=$(DEBUG)
SERVICES:= TR069
SCRIPTFILES := tr069-profile.xml

install:
	@echo -e "\033[32mInstalling progs/services/$(MYNAME) ...\033[0m"
	$(Q)[ -d $(TARGET)/etc/services/$(MYNAME) ] || mkdir -p $(TARGET)/etc/services/$(MYNAME) ;
	$(Q)for i in $(SCRIPTFILES); do cp $$i $(TARGET)/etc/services/$(MYNAME)/.; done
	$(Q)for i in $(SERVICES); do cp $$i.php $(TARGET)/etc/services/.; done
	
.PHONY: install