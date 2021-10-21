# search file from current folder to parent folders
# cutoff at 7th parent folder
search_cutoff := ../../../../../../../
define search-file
	$(if $(filter $(search_cutoff)%,$1),,$(if $(wildcard $1),$1,$(call search-file,../$1)))
endef

include $(call search-file,path.mk)
include $(TOPDIR)/lib.mk

targets :=
current_dir := $(shell pwd)

targets += all
all:
FILES   := ipv6-WANPORT-UP.php control_smart404.php

targets += rootfs
rootfs:
	$(call color_print,Constructing rootfs folders in $(current_dir) ...,green)
	$(call create_install_paths,$(TARGET)/etc/scripts)

targets += install
script_files := $(wildcard *.sh)

install:
	$(call color_print,Installing objects in $(current_dir) ...,green)
	$(call install_scripts,.,$(TARGET)/etc/scripts,$(script_files))
	$(Q)for i in $(FILES); do \
		cp --remove-destination $$i $(TARGET)/etc/scripts/.; \
		chmod +x $(TARGET)/etc/scripts/$$i; \
	done

targets += clean
clean:

.PHONY: $(targets)
