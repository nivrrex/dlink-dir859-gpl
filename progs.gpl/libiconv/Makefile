# search file from current folder to parent folders
# cutoff at 14th parent folder
search_cutoff := ../../../../../../../../../../../../../../
define search-file
	$(if $(filter $(search_cutoff)%,$1),,$(if $(wildcard $1),$1,$(call search-file,../$1)))
endef

include $(call search-file,arch.mk)

# for special toolchain, we need special argument for building PIC object code.
pic_arg :=
ifeq "$(findstring arm-openwrt-linux-,$(CROSS_COMPILE))" "arm-openwrt-linux-"
pic_arg := -fPIC
endif

targets :=
targets += all
all: iconv mkend  

iconv:	
	if [ ! -f ./.configured ]; then \
	cd libiconv-1.9.1 && export CFLAGS=$(pic_arg) && /bin/sh configure --prefix=$(TARGET) --host=$(HOST_TYPE) CC=$(CC) AS=$(AS) AR=$(AR) \
	LD=$(LD) RANLIB=$(RANLIB) STRIP=$(STRIP) --enable-shared=yes; \
	fi	
	cp libiconv-1.9.1/srclib/Makefile_1 libiconv-1.9.1/srclib/Makefile
	make -C libiconv-1.9.1

targets += mkend
mkend:
	touch ./.configured;

targets += install
install:
	if test ! -s "$(TARGET)/lib/libiconv.so.2.2.0"; \
	then cp libiconv-1.9.1/lib/.libs/libiconv.so.2.2.0 $(TARGET)/lib/. ;\
	ln -s -f libiconv.so.2.2.0 $(TARGET)/lib/libiconv.so ;\
	ln -s -f libiconv.so.2.2.0 $(TARGET)/lib/libiconv.so.2 ;\
	fi

targets += clean
clean:
	make -C libiconv-1.9.1 clean
	rm -f .configured

.PHONY: $(targets)
