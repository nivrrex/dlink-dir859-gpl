# search file from current folder to parent folders
# cutoff at 14th parent folder
search_cutoff := ../../../../../../../../../../../../../../
define search-file
	$(if $(filter $(search_cutoff)%,$1),,$(if $(wildcard $1),$1,$(call search-file,../$1)))
endef

include $(call search-file,path.mk)
include $(call search-file,arch.mk)

# for special toolchain, we need special argument for building PIC object code.
pic_arg :=
ifeq "$(findstring arm-openwrt-linux-,$(CROSS_COMPILE))" "arm-openwrt-linux-"
pic_arg := -fPIC
endif

targets :=
targets += all
all: mtd

LZOPATH := lzo-2.06
UUIDPATH := libuuid-1.0.2
MTDUTILPATH := mtd-utils-1.4.5
MTDUTILBUILDDIR := $(CROSS_COMPILE:-=)

LDFLAGS := -L$(TOPDIR)/progs.gpl/mtd-utils/$(UUIDPATH)/.libs

lzo:	
	$(Q)if [ ! -f ./.configured.lzo ]; then \
	cd $(LZOPATH) && export CFLAGS=$(pic_arg) && /bin/sh configure --prefix=$(TARGET) --host=$(HOST_TYPE) CC=$(CC) AS=$(AS) AR=$(AR) \
	LD=$(LD) RANLIB=$(RANLIB) STRIP=$(STRIP) --enable-shared=yes && \
	touch ../.configured.lzo; \
	fi	
	$(Q)make -C $(LZOPATH)

uuid:	
	$(Q)if [ ! -f ./.configured.uuid ]; then \
	cd $(UUIDPATH) && export CFLAGS=$(pic_arg) && /bin/sh configure --prefix=$(TARGET) --host=$(HOST_TYPE) CC=$(CC) AS=$(AS) AR=$(AR) \
	LD=$(LD) RANLIB=$(RANLIB) STRIP=$(STRIP) --enable-shared=yes && \
	touch ../.configured.uuid; \
	fi	
	$(Q)make -C $(UUIDPATH)

mtd: lzo uuid
	# copy zlib include to mtd-utils directory. because mtd will include wrong
	# crc32.h file in zlib directory.
	$(Q)cp $(TOPDIR)/progs.gpl/zlib-1.2.3/zlib.h $(MTDUTILPATH)
	$(Q)cp $(TOPDIR)/progs.gpl/zlib-1.2.3/zconf.h $(MTDUTILPATH)
	$(Q)CROSS=$(CROSS_COMPILE) ZLIBCPPFLAGS=-I$(TOPDIR)/progs.gpl/mtd-utils/$(UUIDPATH) \
	ZLIBLDFLAGS=-L$(TOPDIR)/progs.gpl/zlib-1.2.3 \
	LZOCPPFLAGS=-I$(TOPDIR)/progs.gpl/mtd-utils/$(LZOPATH)/include \
	LZOLDFLAGS=-L$(TOPDIR)/progs.gpl/mtd-utils/$(LZOPATH)/src/.libs \
	LDFLAGS=$(LDFLAGS) \
	WITHOUT_XATTR=1 WITHOUT_LZO=1 \
	make -C $(MTDUTILPATH)

targets += install
install:
	$(Q)cp -af $(LZOPATH)/src/.libs/liblzo2.so* $(TARGET)/lib/
	$(Q)cp -af $(UUIDPATH)/.libs/libuuid.so* $(TARGET)/lib/
	$(Q)cp -af $(MTDUTILPATH)/$(MTDUTILBUILDDIR)/ubi-utils/ubiattach $(TARGET)/usr/sbin/
	$(Q)cp -af $(MTDUTILPATH)/$(MTDUTILBUILDDIR)/ubi-utils/ubidetach $(TARGET)/usr/sbin/
	$(Q)cp -af $(MTDUTILPATH)/$(MTDUTILBUILDDIR)/ubi-utils/ubiformat $(TARGET)/usr/sbin/
	$(Q)cp -af $(MTDUTILPATH)/$(MTDUTILBUILDDIR)/ubi-utils/ubimkvol $(TARGET)/usr/sbin/
	$(Q)cp -af $(MTDUTILPATH)/$(MTDUTILBUILDDIR)/ubi-utils/ubirmvol $(TARGET)/usr/sbin/
	$(Q)cp -af $(MTDUTILPATH)/$(MTDUTILBUILDDIR)/ubi-utils/ubiupdatevol $(TARGET)/usr/sbin/

targets += clean
clean:
	$(Q)make -C $(LZOPATH) clean
	$(Q)make -C $(UUIDPATH) clean
	$(Q)CROSS=$(CROSS_COMPILE) make -C $(MTDUTILPATH) clean
	$(Q)rm -f .configured.lzo
	$(Q)rm -f .configured.uuid

.PHONY: $(targets)
