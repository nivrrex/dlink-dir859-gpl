# vi: set sw=4 ts=4:

include path.mk
include arch.mk
include $(TOPDIR)/.config
include $(TOPDIR)/Vars.mk

#####################################################################
# targets for main Makefile

MYNAME	:= "Aries/dlink.2013gui"
MYMAKE	:= make V=$(V) DEBUG=$(DEBUG)
SUBDIRS	:= defnodes htdocs scripts services events init0.d

ifeq ($(strip $(ELBOX_TEMPLATE_ARIES_MYDLINK_SUPPORT)),y)
SUBDIRS += mydlink
endif

#####################################################################
SUBDIRS	+= $(ELBOX_MODEL_NAME)

all:
	@echo -e "\033[32mBuilding applets for $(MYNAME) ...\033[0m"
	$(Q)for i in $(SUBDIRS); do $(MYMAKE) -C $$i; done

prepare:
	@echo -e "\033[32mPreparing configuration for $(MYNAME) ...\033[0m"

rootfs:
	@echo -e "\033[32mCreate rootfs for $(MYNAME) ...\033[0m"
	$(Q)[ -d $(LTARGET) ] || mkdir -p $(LTARGET)
	$(Q)for i in $(SUBDIRS); do $(MYMAKE) -C $$i rootfs; done

install:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(SUBDIRS); do $(MYMAKE) -C $$i install; done
	#Update the time stamps in *.html *.js *.css *.php files to solve the issues of web page cache.
	find $(TARGET)/htdocs/web/ -type f -name *.html -exec sed -i "s/TimeStamp_QzwsxDcRfvTGByHn/$(shell date +"%Y%m%d%H%M%S")/g" {} \;
	find $(TARGET)/htdocs/web/ -type f -name *.js -exec sed -i "s/TimeStamp_QzwsxDcRfvTGByHn/$(shell date +"%Y%m%d%H%M%S")/g" {} \;
	find $(TARGET)/htdocs/web/ -type f -name *.css -exec sed -i "s/TimeStamp_QzwsxDcRfvTGByHn/$(shell date +"%Y%m%d%H%M%S")/g" {} \;
	find $(TARGET)/htdocs/web/ -type f -name *.php -exec sed -i "s/TimeStamp_QzwsxDcRfvTGByHn/$(shell date +"%Y%m%d%H%M%S")/g" {} \;
	#Uglify the *.html, *.js and *.css files except the MacList.js and localization/*.js.
	find $(TARGET)/htdocs/web/ -type f -name *.html -exec java -jar $(TOPDIR)/progs.brand/java/htmlcompressor-1.5.3.jar  {}  -o {} --compress-js --compress-css \;
	find $(TARGET)/htdocs/web/js ! -path "*/localization/*" -type f -name *.js ! -name MacList.js -exec java -jar $(TOPDIR)/progs.brand/java/yuicompressor-2.4.8.jar {} -o {} \;
	find $(TARGET)/htdocs/web/css -type f -name *.css -exec java -jar $(TOPDIR)/progs.brand/java/yuicompressor-2.4.8.jar {} -o {} \;

install_nouglify:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(SUBDIRS); do $(MYMAKE) -C $$i install; done
	#Update the time stamps in *.html *.js *.css *.php files to solve the issues of web page cache.
	find $(TARGET)/htdocs/web/ -type f -name *.html -exec sed -i "s/TimeStamp_QzwsxDcRfvTGByHn/$(shell date +"%Y%m%d%H%M%S")/g" {} \;
	find $(TARGET)/htdocs/web/ -type f -name *.js -exec sed -i "s/TimeStamp_QzwsxDcRfvTGByHn/$(shell date +"%Y%m%d%H%M%S")/g" {} \;
	find $(TARGET)/htdocs/web/ -type f -name *.css -exec sed -i "s/TimeStamp_QzwsxDcRfvTGByHn/$(shell date +"%Y%m%d%H%M%S")/g" {} \;
	find $(TARGET)/htdocs/web/ -type f -name *.php -exec sed -i "s/TimeStamp_QzwsxDcRfvTGByHn/$(shell date +"%Y%m%d%H%M%S")/g" {} \;

install_org:
	@echo -e "\033[32mInstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(SUBDIRS); do $(MYMAKE) -C $$i install; done

clean:
	@echo -e "\033[32mCleaning board related programs for $(MYNAME) ...\033[0m"
	$(Q)for i in $(SUBDIRS); do $(MYMAKE) -C $$i clean; done

gpl:
	find . -name dir\* | grep -v $(ELBOX_MODEL_NAME) | xargs rm -rf
	mv Makefile.gpl Makefile

license:
	@echo -e "\033[32mMake GPL Notice for $(MYNAME) ...\033[0m"
	$(Q)make -C gpl_notice

license_clean:
	@echo -e "\033[32mClean GPL Notice for $(MYNAME) ...\033[0m"
	$(Q)make -C gpl_notice clean

.PHONY: all install clean prepare rootfs gpl license license_clean

#####################################################################
