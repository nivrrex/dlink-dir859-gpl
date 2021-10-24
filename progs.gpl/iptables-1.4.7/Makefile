# search file from current folder to parent folders
# cutoff at 14th parent folder
search_cutoff := ../../../../../../../../../../../../../../
define search-file
	$(if $(filter $(search_cutoff)%,$1),,$(if $(wildcard $1),$1,$(call search-file,../$1)))
endef

include $(call search-file,arch.mk)
include $(call search-file,path.mk)
include $(call search-file,.config)

CUR_DIR := $(shell pwd)
PREFIX := $(CUR_DIR)/root
APPLET := iptables
TARBALL := iptables-1.4.21.tar.bz2
PATCH := iptables-1.4.21.diff 
SRC_DIR := iptables-1.4.21

CFLAGS += -I$(TOPDIR)/include
export CFLAGS

CONFOPTS := --verbose --target=$(HOST_TYPE) --host=$(HOST_TYPE) \
--prefix=$(PREFIX)/usr --exec-prefix=$(PREFIX)/usr --libdir=$(PREFIX)/lib \
--with-xtlibdir=$(PREFIX)/lib/xtables --disable-devel --enable-shared --disable-libipq --with-kernel=$(KERNELDIR)

ifeq ($(strip $(ELBOX_USE_IPV6)),)
CONFOPTS += --disable-ipv6
else
CONFOPTS += --enable-ipv6
endif

ifeq ($(strip $(BUILD_IPTABLES_SHARED)),y)
	CONFIGCMD += --enable-shared
else
	CONFIGCMD += --disable-shared
endif

.PHONY: all
all: prepare_source
	@echo -e "\033[32mBuilding $(APPLET) ...\033[0m";
	@if test ! -f $(SRC_DIR)/Makefile; then \
		make prepare_source; \
		make config; \
		make -C $(SRC_DIR); \
	else \
		make -C $(SRC_DIR); \
	fi

.PHONY: prepare_source
ifeq ($(SRC_DIR), $(wildcard $(SRC_DIR)))
prepare_source:
else
prepare_source:
	@echo -e "\033[32mExtracting $(APPLET) source codes ...\033[0m";
	@tar jxf $(TARBALL)
	@if test -f $(PATCH); then \
		cat $(PATCH) | patch -p0; \
	fi
endif

# for special toolchain, we need to rewrite makefile for using dynamic loading lib.
# I know this is very ugly, but it is a fast workaround (20140616).
ifeq "$(findstring arm-openwrt-linux-,$(CROSS_COMPILE))" "arm-openwrt-linux-"
define rewrite_makefile
	@sed -i 's/LIBS =/LIBS = -ldl/g' $(CUR_DIR)/$(SRC_DIR)/Makefile
	@sed -i 's/.\/config.status --recheck/-c "echo suppress config recheck"/g' $(CUR_DIR)/$(SRC_DIR)/Makefile
	@sed -i 's/.\/config.status $$@ $$(am__depfiles_maybe)/-c "echo suppress config recheck"/g' $(CUR_DIR)/$(SRC_DIR)/Makefile
	@sed -i 's/.\/config.status $$@/-c "echo suppress config recheck"/g' $(CUR_DIR)/$(SRC_DIR)/Makefile
	@sed -i 's/LIBS =/LIBS = -ldl/g' $(CUR_DIR)/$(SRC_DIR)/iptables/Makefile
	@sed -i 's/.\/config.status $$(subdir)\/$$@ $$(am__depfiles_maybe)/-c "echo suppress config recheck"/g' $(CUR_DIR)/$(SRC_DIR)/iptables/Makefile
	@sed -i 's/$$(MAKE) $$(AM_MAKEFLAGS) am--refresh/echo "suppress config recheck"/g' $(CUR_DIR)/$(SRC_DIR)/iptables/Makefile
endef
else
# just do nothing
define rewrite_makefile
endef
endif

.PHONY: config
config: prepare_source
	@cd $(SRC_DIR); \
		$(CUR_DIR)/$(SRC_DIR)/configure $(CONFOPTS)
	$(call rewrite_makefile)

.PHONY: clean
clean:
	@echo -e "\033[32mCleaning $(APPLET) ...\033[0m"
	@if test -f $(SRC_DIR)/Makefile; then \
		make -C $(SRC_DIR) clean; \
	fi
	@make distclean;

.PHONY: distclean
distclean:
	@if test -d $(SRC_DIR); then rm -rf $(SRC_DIR); fi;
	@if test -d $(PREFIX); then rm -rf $(PREFIX); fi;
	@if test -d ori; then rm -rf ori; fi;

.PHONY: install
install:
	@if [ -f ./$(SRC_DIR)/Makefile ]; then \
		echo -e "\033[32mInstall for $(SRC_DIR)...\033[0m"; \
		[ -d $(PREFIX) ] || mkdir -p $(PREFIX); \
		make -C $(SRC_DIR) install; \
		[ -d $(TARGET)/usr/sbin ] || mkdir -p $(TARGET)/usr/sbin; \
	fi

	# Copy necessary files to rootfs
	cp -af $(PREFIX)/lib/libiptc.so* $(TARGET)/lib/.
	cp -af $(PREFIX)/lib/libip4tc.so* $(TARGET)/lib/.
ifeq ($(strip $(ELBOX_USE_IPV6)),y)
	cp -af $(PREFIX)/lib/libip6tc.so* $(TARGET)/lib/.
endif
	cp -af $(PREFIX)/lib/libxtables.* $(TARGET)/lib/.
	cp -arf $(PREFIX)/lib/xtables $(TARGET)/lib/.
	$(Q)$(STRIP) $(PREFIX)/usr/sbin/ip*
	cp -af $(PREFIX)/usr/sbin/ip* $(TARGET)/usr/sbin/.
	cp -af $(PREFIX)/usr/sbin/xtables-multi $(TARGET)/usr/sbin/.
	
ifeq ($(strip $(ELBOX_IPTABLES_L7_EXT)),y)	
	@echo Nothing to compile, just run \'make install\'
	@echo \(This simply copies this directory into $(TARGET)/etc/l7-protocols \)
	$(Q)mkdir -p $(TARGET)/etc/l7-protocols
	$(Q)cp -R ./layer7_support/l7-protocols-2010-10-19/* $(TARGET)/etc/l7-protocols
	$(Q)find $(TARGET)/etc/l7-protocols -name .svn -type d | xargs rm -rf
endif

.PHONY: diff
diff:
	@mkdir -p ori; \
	tar jxf $(TARBALL) -C ./ori; \
	if test -f $(SRC_DIR)/Makefile; then \
		make -C $(SRC_DIR) distclean; \
	fi; \
	diff -Nru ori/$(SRC_DIR) $(SRC_DIR) > $(PATCH)

.PHONY: showconfig
showconfig:
	@echo CC=$(CC)
	@echo CFLAGS=$(CFLAGS)
	@echo LDFLAGS=$(LDFLAGS)
	@echo TOPDIR=$(TOPDIR)
	@echo TARGET=$(TARGET)
	@echo CONFOPTS=$(CONFOPTS)

