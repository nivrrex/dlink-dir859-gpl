# function: search-file
# $1: filename
#
# search file from current folder to parent folders
# cutoff at 7th parent folder
search_cutoff := ../../../../../../../
define search-file
    $(if $(filter $(search_cutoff)%,$1),,$(if $(wildcard $1),$1,$(call search-file,../$1)))
endef

include $(call search-file,arch.mk)
include $(call search-file,path.mk)
include $(TOPDIR)/.config
include $(TOPDIR)/lib.mk

MYNAME	:= "WRGAC37"
targets :=

targets += all
all: apps 

targets += prepare
prepare:
	$(call color_print,Preparing configuration for $(MYNAME) ...,green)

targets += apps
apps:
	$(call color_print,Building apps for $(MYNAME) ...,green)
	$(call do_make_for_each,,,apps)

targets += rootfs
rootfs:
	$(call color_print,Create rootfs for $(MYNAME) ...,green)
	$(call create_install_paths,$(install_paths))
	@echo $(CONFIG_RGBIN_DEVCONF_MTDBLOCK) >> $(TARGET)/etc/config/devconf
	@echo $(CONFIG_RGBIN_DEVDATA_MTDBLOCK) >> $(TARGET)/etc/config/devdata
ifeq ($(strip $(ELBOX_CREATE_LANGUAGE_PACK)),y)
	@echo "/dev/mtdblock/3" >> $(TARGET)/etc/config/langpack
endif
	#fix the hw version.
	#@echo -n A1 > $(TARGET)/etc/config/hwver

# install board dependent programs
targets += install
install:
	$(call color_print,Installing applets for $(MYNAME) ...,green)
	$(call do_make_for_each,,install,apps)
	$(call install_scripts,., $(TARGET)/usr/sbin,mfc)

# clean board dependent programs
targets += clean
clean:
	$(call color_print,Cleaning applets for $(MYNAME) ...,green)
	$(call do_make_for_each,,clean,apps)

.PHONY: $(targets)
