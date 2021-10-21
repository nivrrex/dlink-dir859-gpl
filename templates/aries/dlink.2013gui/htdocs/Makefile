# search file from current folder to parent folders
# cutoff at 7th parent folder
search_cutoff := ../../../../../../../
define search-file
	$(if $(filter $(search_cutoff)%,$1),,$(if $(wildcard $1),$1,$(call search-file,../$1)))
endef

include $(call search-file,path.mk)
include $(TOPDIR)/lib.mk
include $(TOPDIR)/.config

targets :=

current_dir := $(shell pwd)

targets += all
all:
	$(call color_print,Building objects in $(current_dir) ...,green)

targets += rootfs
rootfs:
	$(call color_print,Constructing rootfs folders in $(current_dir)  ...,green)
	$(call create_install_paths,$(TARGET)/htdocs)

targets += install
copied_dirs := $(subst /,,$(shell ls -d */ 2> /dev/null))

install:
	$(call color_print,Installing objects in $(current_dir) ...,green)
	$(call install_dirs,.,$(TARGET)/htdocs,$(copied_dirs))
	@find $(TARGET)/htdocs -name .svn -type d | xargs rm -rf
	$(Q)ln -sf /htdocs/web/hnap/GetSiteSurvey.xml $(TARGET)/htdocs/web/hnap/GetWirelessSiteSurvey.xml
	$(Q)ln -sf /htdocs/web/js/i18n.js $(TARGET)/htdocs/web/webaccess/js/i18n.js
	$(Q)ln -sf /htdocs/web/js/libajax.js $(TARGET)/htdocs/web/webaccess/js/libajax.js
	$(Q)ln -sf /htdocs/web/js/jquery-1.8.2.min.js $(TARGET)/htdocs/web/webaccess/js/jquery-1.8.2.min.js
	$(Q)ln -sf /htdocs/web/js/jquery.selectbox-0.2_new.js $(TARGET)/htdocs/web/webaccess/js/jquery.selectbox-0.2_new.js
	$(Q)ln -sf /htdocs/web/js/localization $(TARGET)/htdocs/web/webaccess/js/localization
#joel add touch version and builddate in localization
	$(Q)echo -n $(ELBOX_FIRMWARE_VERSION)$(ELBOX_FIRMWARE_REVISION) > $(TARGET)/htdocs/web/js/localization/version
	$(Q)echo -n "$(shell date +"%a %d %b %Y")" > $(TARGET)/htdocs/web/js/localization/builddate
	#Select the response config file of deviceinfo.js from /htdocs/web/config/deviceinfo due to model name.
	find $(TARGET)/htdocs/web/config/deviceinfo -type f -name $(shell echo $(ELBOX_MODEL_NAME)"*" | tr 'a-z' 'A-Z') -exec cp {} $(TARGET)/htdocs/web/config/deviceinfo.js \;
	rm -rf $(TARGET)/htdocs/web/config/deviceinfo

targets += clean
clean:
	$(call color_print,Cleaning objects in $(current_dir) ...,green)

.PHONY: $(targets) 
